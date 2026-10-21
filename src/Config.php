<?php


namespace EasySwoole\Template;


use EasySwoole\Spl\SplBean;

class Config extends SplBean
{
    protected string $serverName = 'EasySwoole';
    protected ?RenderInterface $render = null;
    protected ?string $tempDir = null;
    protected int $workerNum = 3;
    protected int $timeout = 3;

    /** @var callable(\Throwable, mixed...): void|null */
    protected mixed $onException = null;

    /**
     * @return mixed
     */
    public function getRender(): ?RenderInterface
    {
        return $this->render;
    }

    /**
     * @param mixed $render
     */
    public function setRender(RenderInterface $render): void
    {
        $this->render = $render;
    }

    /**
     * @return mixed
     */
    public function getTempDir(): ?string
    {
        return $this->tempDir;
    }

    /**
     * @param mixed $tempDir
     */
    public function setTempDir(string $tempDir): void
    {
        $this->tempDir = $tempDir;
    }

    /**
     * @return int
     */
    public function getWorkerNum(): int
    {
        return $this->workerNum;
    }

    /**
     * @param int $workerNum
     */
    public function setWorkerNum(int $workerNum): void
    {
        if ($workerNum < 1) {
            throw new \InvalidArgumentException('Worker number must be greater than zero.');
        }

        $this->workerNum = $workerNum;
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout(int $timeout): void
    {
        if ($timeout < 1) {
            throw new \InvalidArgumentException('Timeout must be greater than zero.');
        }

        $this->timeout = $timeout;
    }

    /**
     * @return callable(\Throwable, mixed...): void|null
     */
    public function getOnException(): ?callable
    {
        return $this->onException;
    }

    /**
     * @param callable(\Throwable, mixed...): void|null $onException
     */
    public function setOnException(?callable $onException): void
    {
        $this->onException = $onException;
    }

    /**
     * @return string
     */
    public function getServerName(): string
    {
        return $this->serverName;
    }

    /**
     * @param string $serverName
     */
    public function setServerName(string $serverName): void
    {
        $this->serverName = $serverName;
    }

    protected function initialize(): void
    {
        if(empty($this->tempDir)){
            $this->tempDir = sys_get_temp_dir();
        }
    }

}
