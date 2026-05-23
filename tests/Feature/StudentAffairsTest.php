<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\Faculty;
use App\Models\Department;
use App\Models\StudentDocument;
use App\Models\SensitiveDataRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StudentAffairsTest extends TestCase
{
    use RefreshDatabase;

    private $studentAffairsUser;
    private $student;
    private $faculty;
    private $department;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Faculty & Department
        $this->faculty = Faculty::create(['name' => 'كلية التكنولوجيا', 'code' => 'TECH', 'is_active' => true]);
        $this->department = Department::create([
            'name' => 'تكنولوجيا المعلومات',
            'code' => 'IT',
            'faculty_id' => $this->faculty->id,
            'is_active' => true
        ]);

        // Create Student Affairs User
        $this->studentAffairsUser = User::create([
            'name' => 'موظف شئون الطلاب',
            'email' => 'student_affairs@uni.edu',
            'password' => Hash::make('password'),
            'role' => 'student_affairs',
            'permissions' => ['manage_students'],
            'is_active' => true,
        ]);

        // Create Student
        $this->student = Student::create([
            'name' => 'أحمد محمد',
            'national_id' => '30001010101010',
            'reference_number' => 'REF12345',
            'academic_year' => 'الفرقة الأولى',
            'program' => 'تكنولوجيا المعلومات',
            'faculty_id' => $this->faculty->id,
            'department_id' => $this->department->id,
            'status' => 'active',
        ]);
    }

    public function test_student_can_update_profile_directly_for_non_sensitive_fields()
    {
        $response = $this->actingAs($this->student, 'student')
            ->post(route('student.profile.update'), [
                'name' => 'أحمد محمد', // Same
                'national_id' => '30001010101010', // Same
                'phone' => '01012345678',
                'email' => 'student@test.com',
                'address' => 'القاهرة، مصر',
                'academic_year' => 'الفرقة الأولى',
                'program' => 'تكنولوجيا المعلومات',
                'faculty_id' => $this->faculty->id,
                'department_id' => $this->department->id,
            ]);

        $response->assertRedirect(route('student.profile'));
        
        $this->student->refresh();
        $this->assertEquals('01012345678', $this->student->phone);
        $this->assertEquals('student@test.com', $this->student->email);
        $this->assertEquals('القاهرة، مصر', $this->student->address);
        
        // Assert no sensitive requests created
        $this->assertDatabaseEmpty('sensitive_data_requests');
    }

    public function test_student_profile_update_for_sensitive_fields_creates_pending_request()
    {
        $response = $this->actingAs($this->student, 'student')
            ->post(route('student.profile.update'), [
                'name' => 'أحمد محمد علي الجديد', // Changed
                'national_id' => '30001010101010', // Same
                'phone' => '01012345678',
                'email' => 'student@test.com',
                'address' => 'القاهرة، مصر',
                'academic_year' => 'الفرقة الأولى',
                'program' => 'تكنولوجيا المعلومات',
                'faculty_id' => $this->faculty->id,
                'department_id' => $this->department->id,
            ]);

        $response->assertRedirect(route('student.profile'));

        // Profile fields should NOT change immediately
        $this->student->refresh();
        $this->assertEquals('أحمد محمد', $this->student->name);

        // A pending request must exist in database
        $this->assertDatabaseHas('sensitive_data_requests', [
            'student_id' => $this->student->id,
            'status' => 'pending',
        ]);

        $request = SensitiveDataRequest::first();
        $this->assertEquals(['name' => 'أحمد محمد علي الجديد'], $request->requested_data);
    }

    public function test_employee_can_view_student_details()
    {
        $response = $this->actingAs($this->studentAffairsUser)
            ->get(route('affairs.student.show', $this->student));

        $response->assertStatus(200);
        $response->assertSee($this->student->name);
        $response->assertSee($this->student->reference_number);
    }

    public function test_employee_can_approve_sensitive_data_request()
    {
        $request = SensitiveDataRequest::create([
            'student_id' => $this->student->id,
            'requested_data' => ['name' => 'أحمد محمد علي الجديد'],
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->studentAffairsUser)
            ->post(route('affairs.student.process-sensitive-request', $request), [
                'status' => 'approved'
            ]);

        $response->assertRedirect();

        // The request status is updated
        $request->refresh();
        $this->assertEquals('approved', $request->status);
        $this->assertEquals($this->studentAffairsUser->id, $request->reviewed_by);

        // The student profile is updated
        $this->student->refresh();
        $this->assertEquals('أحمد محمد علي الجديد', $this->student->name);
    }

    public function test_employee_can_reject_sensitive_data_request_with_reason()
    {
        $request = SensitiveDataRequest::create([
            'student_id' => $this->student->id,
            'requested_data' => ['name' => 'أحمد محمد علي الجديد'],
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->studentAffairsUser)
            ->post(route('affairs.student.process-sensitive-request', $request), [
                'status' => 'rejected',
                'rejection_reason' => 'الاسم ثلاثي فقط وليس رباعي'
            ]);

        $response->assertRedirect();

        // The request status is updated
        $request->refresh();
        $this->assertEquals('rejected', $request->status);
        $this->assertEquals('الاسم ثلاثي فقط وليس رباعي', $request->rejection_reason);

        // Student name remains unchanged
        $this->student->refresh();
        $this->assertEquals('أحمد محمد', $this->student->name);
    }

    public function test_employee_can_verify_student_document()
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->create('national_id.jpg', 100);
        $path = $file->store('documents');

        $document = StudentDocument::create([
            'student_id' => $this->student->id,
            'type' => 'national_id',
            'file_path' => $path,
            'status' => 'pending',
        ]);

        // Accept the document
        $response = $this->actingAs($this->studentAffairsUser)
            ->post(route('affairs.student.verify-document', $document), [
                'status' => 'verified'
            ]);

        $response->assertRedirect();
        $document->refresh();
        $this->assertEquals('verified', $document->status);

        // Reject the document
        $document->update(['status' => 'pending']);
        $response = $this->actingAs($this->studentAffairsUser)
            ->post(route('affairs.student.verify-document', $document), [
                'status' => 'rejected',
                'rejection_reason' => 'الصورة غير واضحة المعالم'
            ]);

        $response->assertRedirect();
        $document->refresh();
        $this->assertEquals('rejected', $document->status);
        $this->assertEquals('الصورة غير واضحة المعالم', $document->rejection_reason);
    }

    public function test_employee_can_change_academic_status_and_logs_history()
    {
        $response = $this->actingAs($this->studentAffairsUser)
            ->post(route('affairs.student.update-status', $this->student), [
                'status' => 'suspended',
                'notes' => 'تأخر في سداد الرسوم لفترة طويلة'
            ]);

        $response->assertRedirect();

        // Check student status
        $this->student->refresh();
        $this->assertEquals('suspended', $this->student->status);

        // Check that history was logged
        $this->assertDatabaseHas('student_status_histories', [
            'student_id' => $this->student->id,
            'status' => 'suspended',
            'changed_by' => $this->studentAffairsUser->id,
            'notes' => 'تأخر في سداد الرسوم لفترة طويلة'
        ]);
    }

    public function test_employee_can_add_internal_note()
    {
        $response = $this->actingAs($this->studentAffairsUser)
            ->post(route('affairs.student.add-note', $this->student), [
                'note' => 'تم استلام أصل الملف يدوياً بمكتب شؤون الطلاب.'
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('student_notes', [
            'student_id' => $this->student->id,
            'user_id' => $this->studentAffairsUser->id,
            'note' => 'تم استلام أصل الملف يدوياً بمكتب شؤون الطلاب.'
        ]);
    }

    public function test_bulk_status_update_and_bulk_verify_documents()
    {
        // Create second student
        $student2 = Student::create([
            'name' => 'محمد علي',
            'national_id' => '30001010101011',
            'reference_number' => 'REF12346',
            'academic_year' => 'الفرقة الأولى',
            'program' => 'تكنولوجيا المعلومات',
            'faculty_id' => $this->faculty->id,
            'department_id' => $this->department->id,
            'status' => 'active',
        ]);

        // 1. Test Bulk Status Update
        $response = $this->actingAs($this->studentAffairsUser)
            ->post(route('affairs.student.bulk-action'), [
                'student_ids' => [$this->student->id, $student2->id],
                'action_type' => 'update_status',
                'status' => 'graduated',
                'status_notes' => 'تخرج جماعي دفعة 2026'
            ]);

        $response->assertRedirect();

        $this->student->refresh();
        $student2->refresh();
        $this->assertEquals('graduated', $this->student->status);
        $this->assertEquals('graduated', $student2->status);

        $this->assertDatabaseHas('student_status_histories', [
            'student_id' => $this->student->id,
            'status' => 'graduated',
            'notes' => 'تخرج جماعي دفعة 2026'
        ]);

        // 2. Test Bulk Document Verification
        $doc1 = StudentDocument::create([
            'student_id' => $this->student->id,
            'type' => 'national_id',
            'file_path' => 'doc1.jpg',
            'status' => 'pending',
        ]);
        $doc2 = StudentDocument::create([
            'student_id' => $student2->id,
            'type' => 'personal_photo',
            'file_path' => 'doc2.jpg',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->studentAffairsUser)
            ->post(route('affairs.student.bulk-action'), [
                'student_ids' => [$this->student->id, $student2->id],
                'action_type' => 'verify_documents'
            ]);

        $response->assertRedirect();
        
        $doc1->refresh();
        $doc2->refresh();
        $this->assertEquals('verified', $doc1->status);
        $this->assertEquals('verified', $doc2->status);
    }
}
