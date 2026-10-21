<?php

declare(strict_types=1);

namespace EasySwoole\Template\TestCase\Unit;

use EasySwoole\Template\Exception\ClientTimeoutException;
use PHPUnit\Framework\TestCase;

final class ClientTimeoutExceptionTest extends TestCase
{
    public function testExceptionContainsTimeoutAndSocketContext(): void
    {
        $exception = new ClientTimeoutException(1.5, '/tmp/render.sock', 'receive');

        self::assertSame(1.5, $exception->getTimeout());
        self::assertSame('/tmp/render.sock', $exception->getSocketFile());
        self::assertSame('receive', $exception->getOperation());
        self::assertSame(SOCKET_ETIMEDOUT, $exception->getCode());
        self::assertSame(
            'Template client timed out after 1.5 seconds during receive on /tmp/render.sock.',
            $exception->getMessage()
        );
    }
}
