<?php

declare(strict_types=1);

namespace EasySwoole\Template\TestCase\Unit;

use EasySwoole\Template\ClientDeadline;
use EasySwoole\Template\Exception\ClientException;
use EasySwoole\Template\UnixClient;
use PHPUnit\Framework\TestCase;

final class UnixClientTest extends TestCase
{
    public function testNonTimeoutConnectionFailureThrowsClientException(): void
    {
        $socketFile = sys_get_temp_dir() . '/missing-template-' . bin2hex(random_bytes(8)) . '.sock';
        $exception = null;

        \Swoole\Coroutine\run(
            static function () use ($socketFile, &$exception): void {
                try {
                    new UnixClient($socketFile, ClientDeadline::after(1));
                } catch (ClientException $throwable) {
                    $exception = $throwable;
                }
            }
        );

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertSame('connect', $exception->getOperation());
        self::assertSame($socketFile, $exception->getSocketFile());
        self::assertNotSame(SOCKET_ETIMEDOUT, $exception->getErrorCode());
    }
}
