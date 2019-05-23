<?php


namespace EasySwoole\Template;



class Render
{
    protected $config;
    function __construct(Config $config)
    {
        $this->config = $config;
    }

    function attachServer(\swoole_server $server)
    {

    }

    function render(string $template,array $data = [],array $options = []):?string
    {

    }
}