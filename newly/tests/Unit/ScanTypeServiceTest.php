<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ScanTypeService;
use App\Constants\AppConstants;

class ScanTypeServiceTest extends TestCase
{
    protected ScanTypeService $scanTypeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scanTypeService = new ScanTypeService();
    }

    public function test_get_next_scan_type_for_zero_logs(): void
    {
        $result = $this->scanTypeService->getNextScanType(0);

        $this->assertIsArray($result);
        $this->assertEquals('IN', $result['type']);
        $this->assertEquals('AM', $result['category']);
        $this->assertEquals('AM IN', $result['label']);
    }

    public function test_get_next_scan_type_for_one_log(): void
    {
        $result = $this->scanTypeService->getNextScanType(1);

        $this->assertIsArray($result);
        $this->assertEquals('OUT', $result['type']);
        $this->assertEquals('AM', $result['category']);
        $this->assertEquals('AM OUT', $result['label']);
    }

    public function test_get_next_scan_type_for_two_logs(): void
    {
        $result = $this->scanTypeService->getNextScanType(2);

        $this->assertIsArray($result);
        $this->assertEquals('IN', $result['type']);
        $this->assertEquals('PM', $result['category']);
        $this->assertEquals('PM IN', $result['label']);
    }

    public function test_get_next_scan_type_for_three_logs(): void
    {
        $result = $this->scanTypeService->getNextScanType(3);

        $this->assertIsArray($result);
        $this->assertEquals('OUT', $result['type']);
        $this->assertEquals('PM', $result['category']);
        $this->assertEquals('PM OUT', $result['label']);
    }

    public function test_get_next_scan_type_for_four_logs_returns_null(): void
    {
        $result = $this->scanTypeService->getNextScanType(4);

        $this->assertNull($result);
    }

    public function test_get_next_scan_type_for_excessive_logs_returns_null(): void
    {
        $result = $this->scanTypeService->getNextScanType(100);

        $this->assertNull($result);
    }

    public function test_get_all_scan_types_returns_all_four(): void
    {
        $result = $this->scanTypeService->getAllScanTypes();

        $this->assertCount(4, $result);
        $this->assertEquals(AppConstants::SCAN_TYPES, $result);
    }

    public function test_get_max_daily_scans_returns_correct_value(): void
    {
        $result = $this->scanTypeService->getMaxDailyScans();

        $this->assertEquals(AppConstants::MAX_DAILY_SCANS, $result);
        $this->assertEquals(4, $result);
    }

    public function test_can_scan_when_under_limit(): void
    {
        $this->assertTrue($this->scanTypeService->canScan(0));
        $this->assertTrue($this->scanTypeService->canScan(1));
        $this->assertTrue($this->scanTypeService->canScan(2));
        $this->assertTrue($this->scanTypeService->canScan(3));
    }

    public function test_cannot_scan_when_at_limit(): void
    {
        $this->assertFalse($this->scanTypeService->canScan(4));
        $this->assertFalse($this->scanTypeService->canScan(5));
        $this->assertFalse($this->scanTypeService->canScan(100));
    }

    public function test_get_next_scan_index_returns_correct_index(): void
    {
        $this->assertEquals(0, $this->scanTypeService->getNextScanIndex(0));
        $this->assertEquals(1, $this->scanTypeService->getNextScanIndex(1));
        $this->assertEquals(2, $this->scanTypeService->getNextScanIndex(2));
        $this->assertEquals(3, $this->scanTypeService->getNextScanIndex(3));
    }

    public function test_get_next_scan_index_returns_null_when_at_limit(): void
    {
        $this->assertNull($this->scanTypeService->getNextScanIndex(4));
        $this->assertNull($this->scanTypeService->getNextScanIndex(5));
    }
}
