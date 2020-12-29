<?php


namespace EasySwoole\Template;


class Command
{
    const OP_RENDER = 0x1;
    const OP_WORKER_EXIT = 0x2;

    protected $op;
    protected $arg;

    /**
     * @return mixed
     */
    public function getOp()
    {
        return $this->op;
    }

    /**
     * @param mixed $op
     */
    public function setOp($op): void
    {
        $this->op = $op;
    }

    /**
     * @return mixed
     */
    public function getArg()
    {
        return $this->arg;
    }

    /**
     * @param mixed $arg
     */
    public function setArg($arg): void
    {
        $this->arg = $arg;
    }
}