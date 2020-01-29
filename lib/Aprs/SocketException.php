<?php

namespace Aprs;

class SocketException extends \RuntimeException
{
    public function __construct(string $message = "", int $code = null)
    {
        if ($code === null) {
            $code = socket_last_error();
            $message .= ' : ' . socket_strerror($code);
        }
        parent::__construct($message, $code);
    }
}