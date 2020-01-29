<?php
declare(strict_types=1);

namespace Aprs;

use Aprs\Buffer\BufferInterface;

class Connection
{
    private const CONNECTION_MESSAGE_TEMPLATE = 'user %s pass %s vers %version';
    private const FILTER_MESSAGE_TEMPLATE = ' filter %s';
    private const SOFTWARE_VERSION = 'dbursem\\aprs-client';

    /**
     * @var Config
     */
    private $config;

    /** @var bool */
    private $connected;

    /**
     * @var false|resource
     */
    private $socket;

    /**
     * @var BufferInterface
     */
    private $buffer;

    public function __construct(Config $config, BufferInterface $buffer)
    {
        $this->config = $config;
        $this->buffer = $buffer;
        $this->connect();
        $this->send($this->getConnectionMessage());
    }

    private function connect()
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, getprotobyname("tcp"));
        $result = socket_connect($this->socket, $this->config->getHost(), $this->config->getPort());

        if (!$result) {
            socket_close($this->socket);
            throw new SocketException('Connection failed');
        }

        $this->connected = true;
    }

    private function disconnect()
    {
        socket_shutdown($this->socket, 2);
        socket_close($this->socket);
        $this->connected = false;
    }

    private function getConnectionMessage(): string
    {
        $connectionMessage = sprintf(
            self::CONNECTION_MESSAGE_TEMPLATE,
            $this->config->getCallsign(),
            $this->config->getPasscode(),
            self::SOFTWARE_VERSION
        );
        if ($this->config->getFilter() !== '') {
            $connectionMessage .= sprintf(self::FILTER_MESSAGE_TEMPLATE, $this->config->getFilter());
        }
        return $connectionMessage;
    }

    private function send(string $data): void
    {
        $result = socket_send($this->socket, $data, strlen($data), 0);

        if ($result === false) {
            throw new SocketException('Could not send data');
        } elseif ($result === 0) {
            throw new SocketException('No data has been sent', 0);
        }
    }

    public function readSocket()
    {
        $result = socket_select($r = [$this->socket], $w = null, $e = null, 0);
        if ($result === false) {
            throw new SocketException('Could not select socket');
        }
        if ($result === 0) {
            return;
        }

        $result = socket_recv($this->socket, $buf, 8096, 0);
        if ($result === false) {
            throw new SocketException('Receive error');
        } elseif ($result === 0) {
            throw new SocketException('Read 0 after select returned > 0', 0);
        }

        $this->buffer->addResult($result, $buf);
    }
}