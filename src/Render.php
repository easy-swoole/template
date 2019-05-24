<?php


namespace EasySwoole\Template;




use EasySwoole\Component\Singleton;

class Render
{
    use Singleton;

    protected $config;

    function __construct()
    {
        $this->config = new Config();
    }

    public function getConfig():Config
    {
        return $this->config;
    }

    function attachServer(\swoole_server $server)
    {
        $list = $this->generateProcessList();
        foreach ($list as $p){
            $server->addProcess($p->getProcess());
        }
    }

    function render(string $template,array $data = [],array $options = []):?string
    {
        /*
         * 随机找一个进程
         */
        mt_srand();
        $id = rand(1,$this->config->getWorkerNum());
        $sockFile = $this->config->getTempDir()."/Render.{$this->config->getSocketPrefix()}Worker.{$id}.sock";
        $client = new UnixClient($sockFile);
        $client->send(Protocol::pack(serialize([
            'template'=>$template,
            'data'=>$data,
            'options'=>$options
        ])));
        $data = $client->recv($this->config->getTimeout());
        if($data){
            $data = Protocol::unpack($data);
            return unserialize($data);
        }
        return null;
    }

    protected function generateProcessList():array
    {
        $array = [];
        for ($i = 1;$i <= $this->config->getWorkerNum();$i++){
            $array[] = new RenderProcess("Render.{$this->config->getSocketPrefix()}Worker.{$i}",$this->config,false,2,true);;
        }
        return $array;
    }
}