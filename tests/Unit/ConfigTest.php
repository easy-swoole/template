<?php

declare(strict_types=1);

namespace EasySwoole\Template\TestCase\Unit;

use EasySwoole\Template\Config;
use EasySwoole\Template\RenderInterface;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function testDefaultsAreInitialized(): void
    {
        $config = new Config();

        self::assertSame('EasySwoole', $config->getServerName());
        self::assertSame(3, $config->getWorkerNum());
        self::assertSame(3, $config->getTimeout());
        self::assertSame(getcwd(), $config->getTempDir());
        self::assertNull($config->getRender());
        self::assertNull($config->getOnException());
    }

    public function testConfigurationCanBeChanged(): void
    {
        $renderer = new class implements RenderInterface {
            public function render(string $template, array $data = [], array $options = []): ?string
            {
                return $template;
            }

            public function onException(
                \Throwable $throwable,
                string $template,
                array $data = [],
                array $options = []
            ): string {
                return $throwable->getMessage();
            }
        };
        $callback = static function (): void {
        };
        $config = new Config();

        $config->setRender($renderer);
        $config->setTempDir('/tmp/template-tests');
        $config->setWorkerNum(5);
        $config->setTimeout(9);
        $config->setServerName('test-server');
        $config->setOnException($callback);

        self::assertSame($renderer, $config->getRender());
        self::assertSame('/tmp/template-tests', $config->getTempDir());
        self::assertSame(5, $config->getWorkerNum());
        self::assertSame(9, $config->getTimeout());
        self::assertSame('test-server', $config->getServerName());
        self::assertSame($callback, $config->getOnException());
        self::assertSame(md5($renderer::class), $config->getSocketPrefix());
    }

    public function testWorkerNumberMustBeGreaterThanZero(): void
    {
        $config = new Config();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Worker number must be greater than zero.');

        $config->setWorkerNum(0);
    }

    public function testNegativeWorkerNumberIsRejected(): void
    {
        $config = new Config();

        $this->expectException(\InvalidArgumentException::class);

        $config->setWorkerNum(-1);
    }
}
