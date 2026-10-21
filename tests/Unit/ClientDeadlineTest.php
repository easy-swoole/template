<?php

declare(strict_types=1);

namespace EasySwoole\Template\TestCase\Unit;

use EasySwoole\Template\ClientDeadline;
use PHPUnit\Framework\TestCase;

final class ClientDeadlineTest extends TestCase
{
    public function testDeadlineTracksRemainingTime(): void
    {
        $deadline = ClientDeadline::after(1.0);

        self::assertSame(1.0, $deadline->getTimeout());
        self::assertGreaterThan(0.0, $deadline->getRemainingSeconds());
        self::assertLessThanOrEqual(1.0, $deadline->getRemainingSeconds());
        self::assertFalse($deadline->isExpired());
    }

    public function testZeroTimeoutCreatesExpiredDeadline(): void
    {
        $deadline = ClientDeadline::after(0.0);

        self::assertSame(0.0, $deadline->getRemainingSeconds());
        self::assertTrue($deadline->isExpired());
    }
}
