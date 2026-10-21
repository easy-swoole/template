<?php


namespace EasySwoole\Template;


use EasySwoole\Template\Exception\ClientException;
use EasySwoole\Template\Exception\ClientTimeoutException;
use Swoole\Coroutine\Client;

class UnixClient
{
    private Client $client;
    private string $unixSock;

    public function __construct(string $unixSock, ClientDeadline $deadline)
    {
        $this->unixSock = $unixSock;
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
        $timeout = $this->remainingTimeout($deadline, 'connect');
        if (!$this->client->connect($unixSock, 0, $timeout)) {
            $this->throwIfTimedOut($deadline, 'connect');
            throw $this->createClientException('connect');
        }
    }
    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        if($this->client->isConnected()){
            $this->client->close();
        }
    }
    public function send(string $rawData, ClientDeadline $deadline): int
    {
        if($this->client->isConnected()){
            $sentLength = 0;
            $dataLength = strlen($rawData);
            while ($sentLength < $dataLength) {
                $timeout = $this->remainingTimeout($deadline, 'send');
                $sent = $this->client->send(substr($rawData, $sentLength), $timeout);
                if ($sent === false) {
                    $this->throwIfTimedOut($deadline, 'send');
                    throw $this->createClientException('send');
                }
                $sentLength += $sent;
            }
            return $sentLength;
        }else{
            $this->remainingTimeout($deadline, 'send');
            throw $this->createClientException('send');
        }
    }

    public function recv(ClientDeadline $deadline): ?string
    {
        if($this->client->isConnected()){
            $timeout = $this->remainingTimeout($deadline, 'receive');
            $ret = $this->client->recv($timeout);
            if ($ret === false) {
                $this->throwIfTimedOut($deadline, 'receive');
            }
            if ($ret !== false && $ret !== '') {
                return $ret;
            }else{
                return null;
            }
        }else{
            $this->remainingTimeout($deadline, 'receive');
            return null;
        }
    }

    private function remainingTimeout(ClientDeadline $deadline, string $operation): float
    {
        $remaining = $deadline->getRemainingSeconds();
        if ($remaining <= 0) {
            throw new ClientTimeoutException($deadline->getTimeout(), $this->unixSock, $operation);
        }
        return $remaining;
    }

    private function throwIfTimedOut(ClientDeadline $deadline, string $operation): void
    {
        if ($this->client->errCode === SOCKET_ETIMEDOUT || $deadline->isExpired()) {
            throw new ClientTimeoutException($deadline->getTimeout(), $this->unixSock, $operation);
        }
    }

    private function createClientException(string $operation): ClientException
    {
        return new ClientException(
            $operation,
            $this->unixSock,
            $this->client->errCode,
            $this->client->errMsg
        );
    }
}
