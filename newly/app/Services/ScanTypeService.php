<?php

namespace App\Services;

use App\Constants\AppConstants;

class ScanTypeService
{
    public function getNextScanType(int $logCount): ?array
    {
        return AppConstants::SCAN_TYPES[$logCount] ?? null;
    }

    public function getAllScanTypes(): array
    {
        return AppConstants::SCAN_TYPES;
    }

    public function getMaxDailyScans(): int
    {
        return AppConstants::MAX_DAILY_SCANS;
    }

    public function canScan(int $currentLogCount): bool
    {
        return $currentLogCount < AppConstants::MAX_DAILY_SCANS;
    }

    public function getNextScanIndex(int $currentLogCount): ?int
    {
        if ($currentLogCount >= AppConstants::MAX_DAILY_SCANS) {
            return null;
        }
        return $currentLogCount;
    }
}
