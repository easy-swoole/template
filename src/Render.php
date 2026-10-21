<?php

namespace EasySwoole\Template;

use EasySwoole\Component\Process\Socket\UnixProcessConfig;
use Swoole\Server;
use Swoole\Table;

class Render
{
    protected Config $config;
    protected bool $hasAttach = false;

    protected Table $processTable;


    public function __construct(?Config $config = null)
    {
        if($config == null){
            $config = new Config();
        }
        $this->config = $config;
        $this->processTable = new Table(128);
        $this->processTable->column('pid',Table::TYPE_STRING,10);
        $this->processTable->create();
    }

    public function getConfig():Config
    {
        return $this->config;
    }

    public function getProcessTable(): Table
    {
        return $this->processTable;
    }

    public function attachServer(Server $server): void
    {
        if ($this->hasAttach) {
            throw new \LogicException('Render has already been attached to a server.');
        }

        if ($this->getConfig()->getRender() === null) {
            throw new \LogicException('RenderInterface must be configured before attaching the server.');
        }

        $this->hasAttach = true;
        $list = $this->__generateWorkerProcess();
        foreach ($list as $p){
            $server->addProcess($p->getProcess());
        }
    }

    public function render(string $template, array $data = [], array $options = []): ?string
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

    protected function __generateWorkerProcess():array
    {
        $array = [];
        for ($i = 0;$i < $this->getConfig()->getWorkerNum();$i++){
            $config = new UnixProcessConfig();
            $server = $this->getConfig()->getServerName();
            $config->setProcessGroup("{$server}.Render");
            $config->setProcessName("{$server}.Render.Worker.{$i}");
            $config->setSocketFile($this->getConfig()->getTempDir()."/{$server}.Render.Worker.{$i}.sock");
            $config->setArg([
                'config' => $this->config,
                'processTable' => $this->processTable,
            ]);
            $config->setAsyncCallback(false);
            $config->setEnableCoroutine(false);
            $array[$i] = new RenderWorker($config);
        }
        return $array;
    }
}
