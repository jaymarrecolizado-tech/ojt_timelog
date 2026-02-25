<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GuardMockDataSeeder extends Seeder
{
    public function run(): void
    {
        $guardPassword = Hash::make(env('DEFAULT_GUARD_PASSWORD', 'guard123'));
        
        $guards = [
            ['guard_id_no' => 'GUARD-001', 'first_name' => 'Mark', 'last_name' => 'Wilson'],
            ['guard_id_no' => 'GUARD-002', 'first_name' => 'Sarah', 'last_name' => 'Davis'],
            ['guard_id_no' => 'GUARD-003', 'first_name' => 'James', 'last_name' => 'Taylor'],
        ];

        foreach ($guards as $index => $guard) {
            DB::table('users')->insert([
                'id' => Str::uuid(),
                'email' => $guard['guard_id_no'] . '@guard.timelog.com',
                'password_hash' => $guardPassword,
                'role' => 'guard',
                'is_active' => true,
                'email_verified' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
