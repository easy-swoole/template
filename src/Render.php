<?php

namespace EasySwoole\Template;

use EasySwoole\Component\Process\Socket\UnixProcessConfig;
use EasySwoole\Component\Singleton;
use Swoole\Coroutine;
use Swoole\Server;

class Render
{
    use Singleton;

    protected $config;


    function __construct(?Config $config = null)
    {
        if($config == null){
            $config = new Config();
        }
        $this->config = $config;
    }

    public function getConfig():Config
    {
        return $this->config;
    }

    function attachServer(Server $server)
    {
        $list = $this->__generateWorkerProcess();
        foreach ($list as $p){
            $server->addProcess($p->getProcess());
        }
    }

    function render(string $template,?array $data = null,?array $options = null):?string
    {
        /*
         * 随机找一个进程
         */
        mt_srand();
        $id = mt_rand(0,$this->getConfig()->getWorkerNum()-1);
        $server = $this->getConfig()->getServerName();
        $sockFile = $this->getConfig()->getTempDir()."/{$server}.Render.Worker.{$id}.sock";
        $client = new UnixClient($sockFile);
        $com = new Command();
        $com->setOp(Command::OP_RENDER);
        $com->setArg([
            'template'=>$template,
            'data'=>$data,
            'options'=>$options
        ]);
        $client->send(Protocol::pack(serialize($com)));
        $data = $client->recv($this->config->getTimeout());
        if($data){
            $data = Protocol::unpack($data);
            return unserialize($data);
        }
        return null;
    }

    function restartWorker()
    {
        $com = new Command();
        $com->setOp(Command::OP_WORKER_EXIT);
        $data = Protocol::pack(serialize($com));
        $server = $this->getConfig()->getServerName();
        for($i = 0;$i < $this->getConfig()->getWorkerNum();$i++){
            $sockFile = $this->getConfig()->getTempDir()."/{$server}.Render.Worker.{$i}.sock";
            Coroutine::create(function ()use($sockFile,$data){
                $client = new UnixClient($sockFile);
                $client->send($data);
            });
        }
        return true;
    }

    protected function __generateWorkerProcess():array
    {
        $array = [];
        for ($i = 0;$i < $this->getConfig()->getWorkerNum();$i++){
            $config = new UnixProcessConfig();
            $server = $this->getConfig()->getServerName();
            $config->setProcessGroup("{$server}.Render");
            $config->setProcessName("{$server}.Render.Worker.{$i}");
            $config->setSocketFile($this->getConfig()->getTempDir()."/{$server}.Render.Worker.{$i}.sock");
            $config->setArg(['config'=>$this->config]);
            $config->setAsyncCallback(false);
            $array[$i] = new RenderWorker($config);
        }
        return $array;
    }
}