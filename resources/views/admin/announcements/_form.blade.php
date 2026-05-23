<div class="card border-0 shadow-sm" style="max-width:720px;">
    <div class="card-body p-4">
        <form action="{{ $announcement ? route('admin.announcements.update', $announcement) : route('admin.announcements.store') }}" method="POST">
            @csrf
            @if($announcement) @method('PUT') @endif

            <div class="mb-3">
                <label class="form-label fw-bold">العنوان</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $announcement?->title) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">المحتوى</label>
                <textarea name="content" class="form-control" rows="6" required>{{ old('content', $announcement?->content) }}</textarea>
            </div>

            <p class="text-muted small">اترك الحقول التالية فارغة لإعلان عام يظهر لجميع الطلاب</p>
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">الكلية</label>
                    <select name="faculty_id" class="form-select" id="faculty_id">
                        <option value="">— الكل —</option>
                        @foreach($faculties as $f)
                            <option value="{{ $f->id }}" @selected(old('faculty_id', $announcement?->faculty_id) == $f->id)>{{ $f->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">القسم</label>
                    <select name="department_id" class="form-select" id="department_id">
                        <option value="">— الكل —</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">الفرقة</label>
                    <input type="text" name="academic_year" class="form-control" placeholder="مثال: الفرقة الثالثة" value="{{ old('academic_year', $announcement?->academic_year) }}">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">تاريخ الانتهاء (اختياري)</label>
                <input type="datetime-local" name="expires_at" class="form-control" value="{{ old('expires_at', $announcement?->expires_at?->format('Y-m-d\TH:i')) }}">
            </div>
            <div class="mb-3">
                <label class="form-label">الحالة</label>
                <select name="is_published" class="form-select" required>
                    <option value="1" @selected(old('is_published', $announcement?->is_published ?? true))>نشر فوراً</option>
                    <option value="0" @selected(old('is_published', $announcement?->is_published) === false)>مسودة</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">حفظ</button>
            <a href="{{ route('admin.announcements.index') }}" class="btn btn-link">إلغاء</a>
        </form>
    </div>
</div>

@push('scripts')
<script>
const faculties = @json($faculties->map(fn($f) => ['id' => $f->id, 'departments' => $f->activeDepartments]));
const selectedDept = {{ old('department_id', $announcement?->department_id) ?? 'null' }};
const selectedFaculty = {{ old('faculty_id', $announcement?->faculty_id) ?? 'null' }};

function fillDepartments(facultyId) {
    const sel = document.getElementById('department_id');
    sel.innerHTML = '<option value="">— الكل —</option>';
    const f = faculties.find(x => x.id == facultyId);
    if (f) f.departments.forEach(d => {
        const o = document.createElement('option');
        o.value = d.id; o.textContent = d.name;
        if (d.id == selectedDept) o.selected = true;
        sel.appendChild(o);
    });
}
document.getElementById('faculty_id').addEventListener('change', e => fillDepartments(e.target.value));
if (selectedFaculty) fillDepartments(selectedFaculty);
</script>
@endpush
