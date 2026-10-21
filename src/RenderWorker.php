<?php


namespace EasySwoole\Template;


use EasySwoole\Component\Process\Socket\AbstractUnixProcess;
use Swoole\Coroutine\Socket;
use Swoole\Table;

class RenderWorker extends AbstractUnixProcess
{
    protected Config $renderConfig;
    protected Table $processTable;

    public function run($arg): void
    {
        $this->renderConfig = $arg['config'];
        $this->processTable = $arg['processTable'];
        $pid = $this->getPid();
        if ($pid === null) {
            throw new \RuntimeException('Unable to register render worker without a process PID.');
        }
        $this->writeProcessPid($pid);
        parent::run($arg);
    }

    protected function writeProcessPid(int $pid): void
    {
        if (!$this->processTable->set((string)$pid, ['pid' => (string)$pid])) {
            throw new \RuntimeException("Unable to register render worker PID {$pid}.");
        }
    }

    protected function removeProcessPid(int $pid): void
    {
        $this->processTable->del((string)$pid);
    }

    public function onAccept(Socket $socket): void
    {
        /** @var RenderInterface $render */
        $render = $this->renderConfig->getRender();
        $header = $socket->recvAll(4,1);
        if(strlen($header) != 4){
            $socket->close();
            return;
        }
        $allLength = Protocol::packDataLength($header);
        $data = $socket->recvAll($allLength,1);
        if(strlen($data) == $allLength){
            $reply = null;
            $command = unserialize($data);
            if($command instanceof Command){
                switch ($command->getOp()){
                    case Command::OP_RENDER:{
                        $data = $command->getArg();
                        try{
                            $reply = $render->render($data['template'],$data['data'],$data['options']);
                        }catch (\Throwable $throwable){
                            $reply = $render->onException(
                                $throwable,
                                $data['template'],
                                $data['data'],
                                $data['options']
                            );
                        } finally {
                            $socket->sendAll(Protocol::pack(serialize($reply)));
                            $socket->close();
                        }
                        break;
                    }
                    default:{
                        $socket->close();
                    }
                }
            }else{
                $socket->close();
            }
        }else{
            $socket->close();
        }
    }

    protected function onException(\Throwable $throwable, ...$arg): void
    {
        $call = $this->renderConfig->getOnException();
        if(is_callable($call)){
            $call($throwable, ...$arg);
        }else{
            throw $throwable;
        }
    }

    protected function onShutDown(): void
    {
        $pid = $this->getPid();
        if (isset($this->processTable) && $pid !== null) {
            $this->removeProcessPid($pid);
        }

        parent::onShutDown();
    }
}
