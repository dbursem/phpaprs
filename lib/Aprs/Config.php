<?php
declare(strict_types=1);

namespace Aprs;

class Config
{
    /**
     * @var string
     */
    private $host;
    /**
     * @var int
     */
    private $port;
    /**
     * @var string
     */
    private $callsign;
    /**
     * @var string
     */
    private $passcode;
    /**
     * @var string
     */
    private $filter;

    public function __construct(string $host, int $port, string $callsign, ?string $passcode, string $filter)
    {
        $this->host = $host;
        $this->port = $port;
        $this->callsign = $callsign;
        $this->passcode = $passcode;
        $this->filter = $filter;

        if ($passcode === null) {
            $this->passcode = $this->MakePassCode();
        }
    }

    /**
     * Generates an APRS-IS passcode. The algorithm is not public, however some implementations are. This one is based on some of them.
     * @see http://www.aprs-is.net/Connecting.aspx
     */
    private function MakePassCode(): int
    {
        $callsign = strtoupper($this->callsign);
        if (strpos($callsign, '-') !== false) {
            $callsign = (substr($callsign, 0, strpos($callsign, '-')));
        }

        $i = 0;
        $hash = 0x73e2;
        while ($i < strlen($callsign)) {
            $hash ^= ord($callsign[$i++]) << 8;
            $hash ^= ord($callsign[$i++]);
        }
        return ($hash & 0x7fff);
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getCallsign(): string
    {
        return $this->callsign;
    }

    public function getPasscode(): string
    {
        return $this->passcode;
    }

    public function getFilter(): string
    {
        return $this->filter;
    }
}