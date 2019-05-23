<?php


namespace EasySwoole\Template;


use EasySwoole\Component\Process\AbstractProcess;

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
        $ctx = stream_context_create(['socket' => ['so_reuseaddr' => true, 'backlog' => 2048]]);
        $socket = stream_socket_server("unix://$sockFile", $errno, $errStr,STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,$ctx);
        if (!$socket)
        {
            trigger_error($errStr);
            return;
        }
        while (1){
            $reply = null;
            $conn = stream_socket_accept($socket,-1);
            if($conn){
                stream_set_timeout($conn,2);
                $header = fread($conn,4);
                $allLength = Protocol::packDataLength($header);
                $data = fread($conn,$allLength );
                if(strlen($data) == $allLength){
                    $data = unserialize($data);
                }
            }
            fwrite($conn,Protocol::pack(serialize($reply)));
            fclose($conn);
        }
    }

    public function onShutDown()
    {
        // TODO: Implement onShutDown() method.
    }

    public function onReceive(string $str)
    {
        // TODO: Implement onReceive() method.
    }
}