<?php

declare(strict_types=1);

namespace EasySwoole\Template\TestCase\Unit;

use EasySwoole\Template\Protocol;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ProtocolTest extends TestCase
{
    public static function payloadProvider(): iterable
    {
        yield 'empty payload' => [''];
        yield 'plain text' => ['hello'];
        yield 'multibyte text' => ['你好，EasySwoole'];
        yield 'binary payload' => ["\x00\x01\xff\x7f"];
    }

    #[DataProvider('payloadProvider')]
    public function testPacketRoundTripUsesByteLength(string $payload): void
    {
        $packet = Protocol::pack($payload);

        self::assertSame(strlen($payload), Protocol::packDataLength(substr($packet, 0, 4)));
        self::assertSame($payload, Protocol::unpack($packet));
        self::assertSame(strlen($payload) + 4, strlen($packet));
    }

    public function testHeaderUsesNetworkByteOrder(): void
    {
        self::assertSame("\x00\x00\x01\x00", substr(Protocol::pack(str_repeat('a', 256)), 0, 4));
    }
}
