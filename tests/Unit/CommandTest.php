<?php

declare(strict_types=1);

namespace EasySwoole\Template\TestCase\Unit;

use EasySwoole\Template\Command;
use PHPUnit\Framework\TestCase;

final class CommandTest extends TestCase
{
    public function testCommandStoresOperationAndArguments(): void
    {
        $arguments = [
            'template' => 'welcome.php',
            'data' => ['name' => 'EasySwoole'],
            'options' => ['cache' => true],
        ];
        $command = new Command();

        $command->setOp(Command::OP_RENDER);
        $command->setArg($arguments);

        self::assertSame(Command::OP_RENDER, $command->getOp());
        self::assertSame($arguments, $command->getArg());
    }

    public function testCommandSurvivesSerializationRoundTrip(): void
    {
        $command = new Command();
        $command->setOp(Command::OP_WORKER_EXIT);
        $command->setArg(['reason' => 'restart']);

        $copy = unserialize(serialize($command));

        self::assertInstanceOf(Command::class, $copy);
        self::assertSame(Command::OP_WORKER_EXIT, $copy->getOp());
        self::assertSame(['reason' => 'restart'], $copy->getArg());
    }
}
