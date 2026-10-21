<?php

declare(strict_types=1);

namespace Swoole {
    if (!class_exists(Server::class)) {
        class Server
        {
        }
    }
}

namespace EasySwoole\Template\TestCase\Unit {
    use EasySwoole\Template\Config;
    use EasySwoole\Template\Render;
    use EasySwoole\Template\RenderInterface;
    use PHPUnit\Framework\TestCase;
    use Swoole\Server;

    final class RenderTest extends TestCase
    {
        public function testRenderDoesNotProvideSingletonLifecycle(): void
        {
            self::assertFalse(method_exists(Render::class, 'getInstance'));
            self::assertNotSame(new Render(), new Render());
        }

        public function testAttachServerRequiresConfiguredRenderer(): void
        {
            $render = new Render(new Config());
            $server = (new \ReflectionClass(Server::class))->newInstanceWithoutConstructor();

            $this->expectException(\LogicException::class);
            $this->expectExceptionMessage(
                'RenderInterface must be configured before attaching the server.'
            );

            $render->attachServer($server);
        }

        public function testRenderCannotBeAttachedTwice(): void
        {
            $config = new Config();
            $config->setRender(new class implements RenderInterface {
                public function render(string $template, array $data = [], array $options = []): ?string
                {
                    return $template;
                }

                public function onException(
                    \Throwable $throwable,
                    string $template,
                    array $data = [],
                    array $options = []
                ): void {
                }
            });
            $render = new class($config) extends Render {
                protected function __generateWorkerProcess(): array
                {
                    return [];
                }
            };
            $server = (new \ReflectionClass(Server::class))->newInstanceWithoutConstructor();

            $render->attachServer($server);

            $this->expectException(\LogicException::class);
            $this->expectExceptionMessage('Render has already been attached to a server.');

            $render->attachServer($server);
        }

        public function testSharedProcessTableIsPassedToEveryWorker(): void
        {
            $config = new Config();
            $config->setWorkerNum(2);
            $render = new class($config) extends Render {
                public function generateWorkers(): array
                {
                    return $this->__generateWorkerProcess();
                }
            };

            $workers = $render->generateWorkers();

            self::assertCount(2, $workers);
            foreach ($workers as $worker) {
                self::assertSame($render->getProcessTable(), $worker->getArg()['processTable']);
            }
        }
    }
}
