<?php

declare(strict_types=1);

namespace EasySwoole\Template\Exception;

final class ClientTimeoutException extends \RuntimeException
{
    public function __construct(
        private float $timeout,
        private string $socketFile,
        private string $operation
    ) {
        parent::__construct(
            sprintf(
                'Template client timed out after %g seconds during %s on %s.',
                $timeout,
                $operation,
                $socketFile
            ),
            SOCKET_ETIMEDOUT
        );
    }

    public function getTimeout(): float
    {
        return $this->timeout;
    }

    public function getSocketFile(): string
    {
        return $this->socketFile;
    }

    public function getOperation(): string
    {
        return $this->operation;
    }
}
