<?php


namespace EasySwoole\Template;


use EasySwoole\Component\Process\AbstractProcess;
use Swoole\Coroutine\Socket;

class RenderProcess extends AbstractProcess
{
    public function run($arg)
    {
        /** @var Config $arg  */
        $sockFile = $arg->getTempDir()."/{$this->getProcessName()}.sock";
        if (file_exists($sockFile))
        {
            unlink($sockFile);
        }
        $socketServer = new Socket(AF_UNIX,SOCK_STREAM,0);
        $socketServer->bind($sockFile);
        if(!$socketServer->listen(2048)){
            trigger_error('listen '.$sockFile. ' fail');
            return;
        }
        while (1){
            $conn = $socketServer->accept(-1);
            if($conn){
                $header = $conn->recvAll(4,1);
                if(strlen($header) != 4){
                    $conn->close();
                    return;
                }
                $allLength = Protocol::packDataLength($header);
                $data = $conn->recvAll($allLength,1);
                if(strlen($data) == $allLength){
                    $data = unserialize($data);
                    try{
                        $reply = $arg->getRender()->render($data['template'],$data['data'],$data['options']);
                    }catch (\Throwable $throwable){
                        $reply = $arg->getRender()->onException($throwable);
                    }finally{
                        $arg->getRender()->afterRender($reply,$data['template'],$data['data'],$data['options']);
                    }
                    $conn->sendAll(Protocol::pack(serialize($reply)));
                    $conn->close();
                }else{
                    $conn->close();
                    return;
                }
            }else{
                \co::sleep(0.001);
            }
        }
    }

    public function onShutDown()
    {
        // TODO: Implement onShutDown() method.
    }

    public function onReceive(string $str)
    {
        if($str == 'shutdown'){
            $this->getProcess()->exit(0);
        }
    }

    public function onException(\Throwable $throwable)
    {
        trigger_error("{$throwable->getMessage()} at file:{$throwable->getFile()} line:{$throwable->getLine()}");
    }
}