<?php

namespace App\Constants;

class AppConstants
{
    public const DEFAULT_GRACE_PERIOD_MINUTES = 15;
    public const DEFAULT_SCHEDULE_START = '08:00';

    public const PAGINATION_STUDENTS = 20;
    public const PAGINATION_LOGS_ADMIN = 10;
    public const PAGINATION_LOCATIONS = 9;
    public const PAGINATION_LOGS_STUDENT = 15;

    public const MAX_COMPLETION_PERCENT = 100;
    public const MAX_DATE_RANGE_DAYS = 365;
    public const MAX_DAILY_SCANS = 4;
    public const REQUIRED_DAILY_HOURS = 7.5;

    public const QR_TOKEN_EXPIRY_SECONDS = 30;
    public const QR_TOKEN_REUSE_WINDOW_MINUTES = 5;
    public const QR_RANDOM_PART_LENGTH = 16;

    public const PASSWORD_MIN_LENGTH = 12;

    public const SCAN_TYPES = [
        0 => ['type' => 'IN', 'category' => 'AM', 'label' => 'AM IN'],
        1 => ['type' => 'OUT', 'category' => 'AM', 'label' => 'AM OUT'],
        2 => ['type' => 'IN', 'category' => 'PM', 'label' => 'PM IN'],
        3 => ['type' => 'OUT', 'category' => 'PM', 'label' => 'PM OUT'],
    ];

    public const ACCOUNT_LOCKOUT_MAX_ATTEMPTS = 5;
    public const ACCOUNT_LOCKOUT_MINUTES = 30;

    public const RATE_LIMIT_LOGIN_ATTEMPTS = 5;
    public const RATE_LIMIT_LOGIN_MINUTES = 1;
    public const RATE_LIMIT_REGISTER_ATTEMPTS = 3;
    public const RATE_LIMIT_REGISTER_MINUTES = 60;

    public const SESSION_LIFETIME_MINUTES = 120;
    public const CACHE_SETTINGS_TTL = 3600;

    public const STATUS_COMPLETE = 'COMPLETE';
    public const STATUS_INCOMPLETE = 'INCOMPLETE';
    public const STATUS_LATE = 'LATE';

    public const STATUS_MAP = [
        0 => ['status' => 'PENDING', 'class' => 'badge-warning', 'icon' => 'clock'],
        1 => ['status' => 'COMPLETE', 'class' => 'badge-success', 'icon' => 'check-circle'],
        2 => ['status' => 'INCOMPLETE', 'class' => 'badge-danger', 'icon' => 'x-circle'],
    ];
}
