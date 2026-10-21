<?php

declare(strict_types=1);

namespace EasySwoole\Template\Exception;

final class ClientException extends \RuntimeException
{
    public function __construct(
        private string $operation,
        private string $socketFile,
        private int $errorCode,
        private string $errorMessage
    ) {
        parent::__construct(
            sprintf(
                'Template client %s failed on %s: %s [%d].',
                $operation,
                $socketFile,
                $errorMessage !== '' ? $errorMessage : 'Unknown client error',
                $errorCode
            ),
            $errorCode
        );
    }

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function getSocketFile(): string
    {
        return $this->socketFile;
    }

    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}
