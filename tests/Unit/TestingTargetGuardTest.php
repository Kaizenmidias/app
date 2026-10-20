<?php

namespace Tests\Unit;

use App\Support\TestingTargetGuard;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class TestingTargetGuardTest extends TestCase
{
    private function valid(array $overrides = []): callable
    {
        $values = array_merge(TestingTargetGuard::EXPECTED, $overrides);
        return static fn (string $key): string => (string) ($values[$key] ?? '');
    }

    public function test_invalid_target_is_rejected_before_connection(): void
    {
        $this->expectException(RuntimeException::class);
        TestingTargetGuard::validateEnvironment($this->valid(['DB_DATABASE' => 'kaizen_partners']));
    }

    public function test_secondary_url_or_socket_override_is_rejected(): void
    {
        $this->expectException(RuntimeException::class);
        TestingTargetGuard::validateEnvironment($this->valid(['DB_URL' => 'mysql://production']));
    }

    public function test_valid_target_is_distinguishable(): void
    {
        TestingTargetGuard::validateEnvironment($this->valid());
        $this->assertTrue(true);
    }
}
