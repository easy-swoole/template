<?php


namespace EasySwoole\Template;


use EasySwoole\Spl\SplBean;

class Config extends SplBean
{
    protected $serverName = 'EasySwoole';
    protected $render;
    protected $tempDir;
    protected $workerNum = 3;
    protected $timeout = 3;
    /** @var callable|null */
    protected $onException;

    /**
     * @return mixed
     */
    public function getRender():RenderInterface
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
    public function getTempDir()
    {
        return $this->tempDir;
    }

    /**
     * @param mixed $tempDir
     */
    public function setTempDir($tempDir): void
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
        $this->timeout = $timeout;
    }

    /**
     * @return mixed
     */
    public function getSocketPrefix()
    {
        return md5(get_class($this->getRender()));
    }

    /**
     * @return callable|null
     */
    public function getOnException(): ?callable
    {
        return $this->onException;
    }

    /**
     * @param callable|null $onException
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
            $this->tempDir = getcwd();
        }
    }

}