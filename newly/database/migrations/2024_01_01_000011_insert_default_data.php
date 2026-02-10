<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Insert default location
        DB::table('locations')->insert([
            'id' => Str::uuid(),
            'name' => 'Main Gate',
            'description' => 'Primary entrance/exit point',
            'secret_key' => 'default-secret-key-change-me',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert default super admin
        $adminId = Str::uuid();
        DB::table('users')->insert([
            'id' => $adminId,
            'email' => 'admin@ojt-tlms.test',
            'password_hash' => Hash::make('Admin@123'),
            'role' => 'super_admin',
            'is_active' => true,
            'email_verified' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert default system settings
        $settings = [
            ['setting_key' => 'qr_rotation_seconds', 'setting_value' => '30', 'data_type' => 'number', 'description' => 'QR code rotation interval in seconds'],
            ['setting_key' => 'max_scans_per_day', 'setting_value' => '4', 'data_type' => 'number', 'description' => 'Maximum scans allowed per student per day'],
            ['setting_key' => 'grace_period_minutes', 'setting_value' => '15', 'data_type' => 'number', 'description' => 'Minutes after schedule start to not be marked late'],
            ['setting_key' => 'schedule_am_start', 'setting_value' => '08:00', 'data_type' => 'string', 'description' => 'AM schedule start time'],
            ['setting_key' => 'schedule_am_end', 'setting_value' => '12:00', 'data_type' => 'string', 'description' => 'AM schedule end time'],
            ['setting_key' => 'schedule_pm_start', 'setting_value' => '13:00', 'data_type' => 'string', 'description' => 'PM schedule start time'],
            ['setting_key' => 'schedule_pm_end', 'setting_value' => '17:00', 'data_type' => 'string', 'description' => 'PM schedule end time'],
            ['setting_key' => 'geolocation_required', 'setting_value' => 'false', 'data_type' => 'boolean', 'description' => 'Whether GPS validation is required'],
            ['setting_key' => 'geolocation_max_distance', 'setting_value' => '200', 'data_type' => 'number', 'description' => 'Max allowed distance from site in meters'],
            ['setting_key' => 'scan_debounce_seconds', 'setting_value' => '60', 'data_type' => 'number', 'description' => 'Minimum seconds between scans for same student'],
        ];

        foreach ($settings as $setting) {
            DB::table('system_settings')->insert(array_merge($setting, [
                'id' => Str::uuid(),
                'updated_by' => $adminId,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        DB::table('system_settings')->truncate();
        DB::table('users')->where('email', 'admin@ojt-tlms.test')->delete();
        DB::table('locations')->where('name', 'Main Gate')->delete();
    }
};
