<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentMockDataSeeder extends Seeder
{
    public function run(): void
    {
        $studentPassword = Hash::make(env('DEFAULT_STUDENT_PASSWORD', 'student123'));
        $locationId = DB::table('locations')->first()->id ?? Str::uuid();
        
        $students = [
            ['student_id_no' => '2024-001', 'first_name' => 'John', 'last_name' => 'Smith'],
            ['student_id_no' => '2024-002', 'first_name' => 'Jane', 'last_name' => 'Doe'],
            ['student_id_no' => '2024-003', 'first_name' => 'Michael', 'last_name' => 'Johnson'],
            ['student_id_no' => '2024-004', 'first_name' => 'Emily', 'last_name' => 'Williams'],
            ['student_id_no' => '2024-005', 'first_name' => 'David', 'last_name' => 'Brown'],
        ];

        $studentIds = [];
        foreach ($students as $index => $student) {
            $userId = Str::uuid();
            $studentId = Str::uuid();
            $studentIds[] = $studentId;
            
            DB::table('users')->insert([
                'id' => $userId,
                'email' => $student['student_id_no'] . '@student.timelog.com',
                'password_hash' => $studentPassword,
                'role' => 'student',
                'is_active' => true,
                'email_verified' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('students')->insert([
                'id' => $studentId,
                'user_id' => $userId,
                'student_id_no' => $student['student_id_no'],
                'first_name' => $student['first_name'],
                'last_name' => $student['last_name'],
                'department' => 'Computer Science',
                'program' => 'BS Computer Science',
                'school_university' => 'University of Technology',
                'company' => 'Tech Solutions Inc.',
                'company_address' => '123 Tech Street, Manila',
                'supervisor_name' => 'Mr. Robert Lee',
                'ojt_start' => '2026-01-01',
                'ojt_end' => '2026-06-30',
                'required_hours' => 500.00,
                'contact_no' => '0912345678' . str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $logTypes = [
            ['type' => 'IN', 'category' => 'AM'],
            ['type' => 'OUT', 'category' => 'AM'],
            ['type' => 'IN', 'category' => 'PM'],
            ['type' => 'OUT', 'category' => 'PM'],
        ];
        
        foreach ($studentIds as $index => $studentId) {
            for ($day = 1; $day <= 30; $day++) {
                $date = sprintf('2026-01-%02d', $day);
                $dayOfWeek = date('N', strtotime($date));
                
                if ($dayOfWeek >= 6) continue;
                
                $baseHour = 7 + ($index % 3);
                $baseMinute = 30 + ($index * 10) % 30;
                $randomOffset = ($index * $day) % 15;
                
                $times = [
                    ['type' => 'IN', 'category' => 'AM', 'hour' => $baseHour, 'minute' => $baseMinute + $randomOffset],
                    ['type' => 'OUT', 'category' => 'AM', 'hour' => 12, 'minute' => 0],
                    ['type' => 'IN', 'category' => 'PM', 'hour' => 13, 'minute' => 0 + $randomOffset],
                    ['type' => 'OUT', 'category' => 'PM', 'hour' => 17 + ($index % 2), 'minute' => 0 + intdiv($randomOffset, 2)],
                ];
                
                foreach ($times as $time) {
                    $timestamp = $date . ' ' . sprintf('%02d:%02d:00', $time['hour'], $time['minute']);
                    
                    DB::table('time_logs')->insert([
                        'id' => Str::uuid(),
                        'student_id' => $studentId,
                        'log_type' => $time['type'],
                        'log_category' => $time['category'],
                        'timestamp' => $timestamp,
                        'date' => $date,
                        'location_id' => $locationId,
                        'latitude' => 14.5995 + (rand(-100, 100) / 100000),
                        'longitude' => 120.9842 + (rand(-100, 100) / 100000),
                        'device_info' => 'Test Device ' . ($index + 1),
                        'is_manual' => false,
                        'is_flagged' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
