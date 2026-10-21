<?php

declare(strict_types=1);

namespace EasySwoole\Template\TestCase\Unit;

use EasySwoole\Template\Config;
use EasySwoole\Template\RenderWorker;
use PHPUnit\Framework\TestCase;

final class RenderWorkerTest extends TestCase
{
    public function testProcessExceptionCallbackReceivesThrowableAndAllContext(): void
    {
        $received = [];
        $config = new Config();
        $config->setOnException(
            static function (\Throwable $throwable, ...$context) use (&$received): void {
                $received = [$throwable, ...$context];
            }
        );
        $exception = new \RuntimeException('worker failed');
        $socketContext = new \stdClass();

        $workerReflection = new \ReflectionClass(RenderWorker::class);
        $worker = $workerReflection->newInstanceWithoutConstructor();
        $configProperty = $workerReflection->getProperty('renderConfig');
        $configProperty->setValue($worker, $config);
        $onException = $workerReflection->getMethod('onException');

        $onException->invoke($worker, $exception, 'run', $socketContext);

        self::assertSame([$exception, 'run', $socketContext], $received);
    }

    public function testProcessExceptionIsRethrownWithoutCallback(): void
    {
        $config = new Config();
        $exception = new \RuntimeException('worker failed');
        $workerReflection = new \ReflectionClass(RenderWorker::class);
        $worker = $workerReflection->newInstanceWithoutConstructor();
        $workerReflection->getProperty('renderConfig')->setValue($worker, $config);

        $this->expectExceptionObject($exception);

        $workerReflection->getMethod('onException')->invoke($worker, $exception);
    }

}
