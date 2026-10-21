<?php


namespace EasySwoole\Template;


use Swoole\Coroutine\Client;

class UnixClient
{
    private Client $client;

    public function __construct(string $unixSock)
    {
        $this->client = new Client(SWOOLE_UNIX_STREAM);
        $this->client->set(
            [
                'open_length_check' => true,
                'package_length_type'   => 'N',
                'package_length_offset' => 0,
                'package_body_offset'   => 4,
                'package_max_length'    => 1024*1024
            ]
        );
        $this->client->connect($unixSock,0,3);
    }
    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        if($this->client->isConnected()){
            $this->client->close();
        }
    }
    public function send(string $rawData): int|false
    {
        if($this->client->isConnected()){
            return $this->client->send($rawData);
        }else{
            return false;
        }
    }
    public function recv(float $timeout = 0.1): ?string
    {
        if($this->client->isConnected()){
            $ret = $this->client->recv($timeout);
            if(!empty($ret)){
                return $ret;
            }else{
                return null;
            }
        }else{
            return null;
        }
    }
}
