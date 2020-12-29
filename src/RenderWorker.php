<?php


namespace EasySwoole\Template;


use EasySwoole\Component\Process\Socket\AbstractUnixProcess;
use Swoole\Coroutine;
use Swoole\Coroutine\Socket;

class RenderWorker extends AbstractUnixProcess
{
    /** @var Config */
    protected $renderConfig;
    public function run($arg)
    {
        $this->renderConfig = $arg['config'];
        parent::run($arg);
    }

    function onAccept(Socket $socket)
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
                            $reply = $render->onException($throwable,$data);
                        } finally {
                            $socket->sendAll(Protocol::pack(serialize($reply)));
                            $socket->close();
                        }
                        break;
                    }
                    case Command::OP_WORKER_EXIT:{
                        Coroutine::create(function (){
                            Coroutine::sleep(0.001);
                            $this->getProcess()->exit(0);
                        });
                        $reply = true;
                        $socket->sendAll(Protocol::pack(serialize($reply)));
                        $socket->close();
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

    protected function onException(\Throwable $throwable,...$arg)
    {
        $call = $this->renderConfig->getOnException();
        if(is_callable($call)){
            call_user_func($call);
        }else{
            throw $throwable;
        }
    }
}