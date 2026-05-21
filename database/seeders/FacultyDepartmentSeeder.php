<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Faculty;
use App\Models\Department;

class FacultyDepartmentSeeder extends Seeder
{
    /**
     * Seed the faculties and departments.
     */
    public function run(): void
    {
        $faculty = Faculty::updateOrCreate(
            ['code' => 'FIE'],
            [
                'name' => 'Faculty of Industry and Energy Technology',
                'description' => 'Faculty of Industry and Energy Technology',
                'is_active' => true,
            ]
        );

        $departments = [
            ['name' => 'Information Technology', 'code' => 'IT'],
            ['name' => 'Mechatronics', 'code' => 'MECHA'],
            ['name' => 'Autotronics', 'code' => 'AUTO'],
            ['name' => 'Renewable Energy', 'code' => 'RENEW'],
        ];

        foreach ($departments as $department) {
            Department::updateOrCreate(
                ['faculty_id' => $faculty->id, 'code' => $department['code']],
                [
                    'faculty_id' => $faculty->id,
                    'name' => $department['name'],
                    'is_active' => true,
                ]
            );
        }
    }
}
