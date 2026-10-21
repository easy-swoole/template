<?php

declare(strict_types=1);

namespace EasySwoole\Template;

final class ClientDeadline
{
    private float $expiresAt;

    private function __construct(private float $timeout)
    {
        $this->expiresAt = hrtime(true) / 1_000_000_000 + $timeout;
    }

    public static function after(float $timeout): self
    {
        return new self($timeout);
    }

    public function getTimeout(): float
    {
        return $this->timeout;
    }

    public function getRemainingSeconds(): float
    {
        return max(0.0, $this->expiresAt - hrtime(true) / 1_000_000_000);
    }

    public function isExpired(): bool
    {
        return $this->getRemainingSeconds() <= 0;
    }
}
