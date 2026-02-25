<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class SampleAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $students = $this->createStudents();
        $this->createTimeLogs($students);
        $this->command->info('Created ' . count($students) . ' students with attendance records.');
    }

    private function createStudents(): array
    {
        $studentsData = [
            [
                'student_id_no' => '2024-001',
                'first_name' => 'Juan',
                'middle_name' => 'Santos',
                'last_name' => 'Dela Cruz',
                'department' => 'Information Technology',
                'program' => 'Bachelor of Science in Information Technology',
                'school_university' => 'Polytechnic University of the Philippines',
                'company' => 'Tech Innovations Inc.',
                'company_address' => 'BGC, Taguig City',
                'supervisor_name' => 'Maria Santos',
                'contact_no' => '0917-123-4567',
                'ojt_start' => '2026-01-06',
                'ojt_end' => '2026-03-31',
                'required_hours' => 500.00,
            ],
            [
                'student_id_no' => '2024-002',
                'first_name' => 'Maria',
                'middle_name' => 'Reyes',
                'last_name' => 'Garcia',
                'department' => 'Information Technology',
                'program' => 'Bachelor of Science in Computer Science',
                'school_university' => 'University of the Philippines',
                'company' => 'Digital Solutions Corp.',
                'company_address' => 'Makati City',
                'supervisor_name' => 'John Cruz',
                'contact_no' => '0918-234-5678',
                'ojt_start' => '2026-01-06',
                'ojt_end' => '2026-03-31',
                'required_hours' => 500.00,
            ],
            [
                'student_id_no' => '2024-003',
                'first_name' => 'Pedro',
                'middle_name' => null,
                'last_name' => 'Santos',
                'department' => 'Information Technology',
                'program' => 'Bachelor of Science in Information Systems',
                'school_university' => 'De La Salle University',
                'company' => 'CodeCraft Labs',
                'company_address' => 'Quezon City',
                'supervisor_name' => 'Ana Reyes',
                'contact_no' => '0919-345-6789',
                'ojt_start' => '2026-01-06',
                'ojt_end' => '2026-03-31',
                'required_hours' => 500.00,
            ],
            [
                'student_id_no' => '2024-004',
                'first_name' => 'Ana',
                'middle_name' => 'Cruz',
                'last_name' => 'Reyes',
                'department' => 'Information Technology',
                'program' => 'Bachelor of Science in Information Technology',
                'school_university' => 'Ateneo de Manila University',
                'company' => 'WebDev Studios',
                'company_address' => 'Pasig City',
                'supervisor_name' => 'Carlos Garcia',
                'contact_no' => '0920-456-7890',
                'ojt_start' => '2026-01-06',
                'ojt_end' => '2026-03-31',
                'required_hours' => 500.00,
            ],
            [
                'student_id_no' => '2024-005',
                'first_name' => 'Carlos',
                'middle_name' => 'Garcia',
                'last_name' => 'Mendoza',
                'department' => 'Information Technology',
                'program' => 'Bachelor of Science in Computer Science',
                'school_university' => 'University of Santo Tomas',
                'company' => 'AppWorks Philippines',
                'company_address' => 'Ortigas, Pasig City',
                'supervisor_name' => 'Elena Torres',
                'contact_no' => '0921-567-8901',
                'ojt_start' => '2026-01-06',
                'ojt_end' => '2026-03-31',
                'required_hours' => 500.00,
            ],
        ];

        $students = [];
        $password = Hash::make('Student@123');

        foreach ($studentsData as $data) {
            $userId = (string) Str::uuid();
            $studentId = (string) Str::uuid();

            DB::table('users')->insert([
                'id' => $userId,
                'email' => $data['student_id_no'] . '@student.timelog.com',
                'password_hash' => $password,
                'role' => 'student',
                'is_active' => true,
                'email_verified' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('students')->insert([
                'id' => $studentId,
                'user_id' => $userId,
                'student_id_no' => $data['student_id_no'],
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'],
                'last_name' => $data['last_name'],
                'suffix' => null,
                'department' => $data['department'],
                'program' => $data['program'],
                'school_university' => $data['school_university'],
                'company' => $data['company'],
                'company_address' => $data['company_address'],
                'supervisor_name' => $data['supervisor_name'],
                'ojt_start' => $data['ojt_start'],
                'ojt_end' => $data['ojt_end'],
                'required_hours' => $data['required_hours'],
                'contact_no' => $data['contact_no'],
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $students[] = [
                'id' => $studentId,
                'student_id_no' => $data['student_id_no'],
                'name' => $data['first_name'] . ' ' . $data['last_name'],
            ];
        }

        return $students;
    }

    private function createTimeLogs(array $students): void
    {
        $startDate = now()->parse('2026-02-01');
        $endDate = now()->parse('2026-02-24');

        foreach ($students as $student) {
            $currentDate = $startDate->copy();
            $index = 0;

            while ($currentDate->lte($endDate)) {
                if ($currentDate->isWeekday()) {
                    $this->createDailyLogs($student['id'], $currentDate, $index);
                    $index += 4;
                }
                $currentDate->addDay();
            }
        }
    }

    private function createDailyLogs(string $studentId, $date, int $baseIndex): void
    {
        $baseTime = $date->copy()->setTime(8, 0, 0);

        $logs = [
            ['type' => 'IN', 'category' => 'AM', 'offset' => 0],
            ['type' => 'OUT', 'category' => 'AM', 'offset' => 4],
            ['type' => 'IN', 'category' => 'PM', 'offset' => 5],
            ['type' => 'OUT', 'category' => 'PM', 'offset' => 9],
        ];

        foreach ($logs as $log) {
            $timestamp = $baseTime->copy()->addHours($log['offset'])->addMinutes(rand(-15, 15));

            DB::table('time_logs')->insert([
                'id' => (string) Str::uuid(),
                'student_id' => $studentId,
                'log_type' => $log['type'],
                'log_category' => $log['category'],
                'timestamp' => $timestamp,
                'date' => $date->toDateString(),
                'qr_token_hash' => hash('sha256', Str::random(32)),
                'location_id' => null,
                'latitude' => 14.5995 + (rand(-100, 100) / 10000),
                'longitude' => 120.9842 + (rand(-100, 100) / 10000),
                'device_info' => 'Mobile App v1.0',
                'ip_address' => '192.168.1.' . rand(1, 254),
                'is_manual' => false,
                'is_flagged' => false,
                'flag_reason' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }
    }
}
