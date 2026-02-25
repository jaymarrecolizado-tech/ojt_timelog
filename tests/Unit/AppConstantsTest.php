<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Constants\AppConstants;

class AppConstantsTest extends TestCase
{
    public function test_pagination_constants_exist(): void
    {
        $this->assertIsInt(AppConstants::PAGINATION_STUDENTS);
        $this->assertIsInt(AppConstants::PAGINATION_LOGS_ADMIN);
        $this->assertIsInt(AppConstants::PAGINATION_LOCATIONS);
        $this->assertIsInt(AppConstants::PAGINATION_LOGS_STUDENT);
    }

    public function test_qr_token_constants_exist(): void
    {
        $this->assertIsInt(AppConstants::QR_TOKEN_EXPIRY_SECONDS);
        $this->assertIsInt(AppConstants::QR_TOKEN_REUSE_WINDOW_MINUTES);
        $this->assertIsInt(AppConstants::QR_RANDOM_PART_LENGTH);
    }

    public function test_password_min_length_constant(): void
    {
        $this->assertIsInt(AppConstants::PASSWORD_MIN_LENGTH);
        $this->assertGreaterThanOrEqual(12, AppConstants::PASSWORD_MIN_LENGTH);
    }

    public function test_scan_types_array_has_correct_count(): void
    {
        $this->assertCount(4, AppConstants::SCAN_TYPES);
    }

    public function test_scan_types_have_required_keys(): void
    {
        foreach (AppConstants::SCAN_TYPES as $scanType) {
            $this->assertArrayHasKey('type', $scanType);
            $this->assertArrayHasKey('category', $scanType);
            $this->assertArrayHasKey('label', $scanType);
        }
    }

    public function test_scan_types_labels_are_correct(): void
    {
        $this->assertEquals('AM IN', AppConstants::SCAN_TYPES[0]['label']);
        $this->assertEquals('AM OUT', AppConstants::SCAN_TYPES[1]['label']);
        $this->assertEquals('PM IN', AppConstants::SCAN_TYPES[2]['label']);
        $this->assertEquals('PM OUT', AppConstants::SCAN_TYPES[3]['label']);
    }

    public function test_default_grace_period_is_valid(): void
    {
        $this->assertIsInt(AppConstants::DEFAULT_GRACE_PERIOD_MINUTES);
        $this->assertGreaterThan(0, AppConstants::DEFAULT_GRACE_PERIOD_MINUTES);
    }

    public function test_max_daily_scans_is_valid(): void
    {
        $this->assertIsInt(AppConstants::MAX_DAILY_SCANS);
        $this->assertGreaterThan(0, AppConstants::MAX_DAILY_SCANS);
    }

    public function test_required_daily_hours_is_valid(): void
    {
        $this->assertIsFloat(AppConstants::REQUIRED_DAILY_HOURS);
        $this->assertGreaterThan(0, AppConstants::REQUIRED_DAILY_HOURS);
    }

    public function test_account_lockout_constants_exist(): void
    {
        $this->assertIsInt(AppConstants::ACCOUNT_LOCKOUT_MAX_ATTEMPTS);
        $this->assertIsInt(AppConstants::ACCOUNT_LOCKOUT_MINUTES);
    }

    public function test_status_constants_exist(): void
    {
        $this->assertEquals('COMPLETE', AppConstants::STATUS_COMPLETE);
        $this->assertEquals('INCOMPLETE', AppConstants::STATUS_INCOMPLETE);
        $this->assertEquals('LATE', AppConstants::STATUS_LATE);
    }

    public function test_rate_limit_constants_exist(): void
    {
        $this->assertIsInt(AppConstants::RATE_LIMIT_LOGIN_ATTEMPTS);
        $this->assertIsInt(AppConstants::RATE_LIMIT_LOGIN_MINUTES);
        $this->assertIsInt(AppConstants::RATE_LIMIT_REGISTER_ATTEMPTS);
        $this->assertIsInt(AppConstants::RATE_LIMIT_REGISTER_MINUTES);
    }

    public function test_cache_ttl_is_valid(): void
    {
        $this->assertIsInt(AppConstants::CACHE_SETTINGS_TTL);
        $this->assertGreaterThan(0, AppConstants::CACHE_SETTINGS_TTL);
    }

    public function test_max_date_range_days_is_valid(): void
    {
        $this->assertIsInt(AppConstants::MAX_DATE_RANGE_DAYS);
        $this->assertGreaterThan(0, AppConstants::MAX_DATE_RANGE_DAYS);
    }
}
