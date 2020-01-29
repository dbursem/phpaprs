<?php

namespace Aprs;

class InvalidMessageException extends \RuntimeException
{
    public function __construct(string $rawMessage)
    {
        parent::__construct('RawMessage is not a valid APRS message: ' . $rawMessage);
    }
}