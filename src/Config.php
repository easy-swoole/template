<?php


namespace EasySwoole\Template;


use EasySwoole\Spl\SplBean;

class Config extends SplBean
{
    protected $render;
    protected $tempDir;

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

    protected function initialize(): void
    {
        if(empty($this->tempDir)){
            $this->tempDir = sys_get_temp_dir();
        }
    }

}