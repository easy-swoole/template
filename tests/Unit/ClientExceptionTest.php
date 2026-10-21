<?php

declare(strict_types=1);

namespace EasySwoole\Template\TestCase\Unit;

use EasySwoole\Template\Exception\ClientException;
use PHPUnit\Framework\TestCase;

final class ClientExceptionTest extends TestCase
{
    public function testExceptionContainsClientFailureContext(): void
    {
        $exception = new ClientException('connect', '/tmp/render.sock', 2, 'No such file or directory');

        self::assertSame('connect', $exception->getOperation());
        self::assertSame('/tmp/render.sock', $exception->getSocketFile());
        self::assertSame(2, $exception->getErrorCode());
        self::assertSame('No such file or directory', $exception->getErrorMessage());
        self::assertSame(2, $exception->getCode());
        self::assertSame(
            'Template client connect failed on /tmp/render.sock: No such file or directory [2].',
            $exception->getMessage()
        );
    }
}
