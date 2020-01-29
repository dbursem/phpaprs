<?php


namespace Aprs;


class MessageContainer
{
    /**
     * @var string
     */
    private $src;
    /**
     * @var string
     */
    private $fullPath;
    /**
     * @var array
     */
    private $path;
    /**
     * @var string
     */
    private $code;
    /**
     * @var string
     */
    private $aprsData;


    public function __construct(string $src, string $fullpath, array $path, string $code, string $aprsData)
    {
        $this->src = $src;
        $this->fullPath = $fullpath;
        $this->path = $path;
        $this->code = $code;
        $this->aprsData = $aprsData;
    }


    public static function createFromRawMessage(string $rawMessage): ?self
    {
        if ($rawMessage[0] == '#') {
            return null;
        }

        $result = preg_match('/^([^>]+)>([^:]+):(.)(.+)$/', $rawMessage, $matches);

        if ($result === false) {
            throw new \LogicException();
        } elseif ($result === 0) {
            throw new InvalidMessageException($rawMessage);
        }

        $src = $matches[1];
        $fullPath = $matches[2];
        $path = explode(',', $fullPath);
        $code = $matches[3];
        $aprsData = $matches[4];

        return new self($src, $fullPath, $path, $code, $aprsData);
    }

}


RewriteEngine On
RewriteCond %{HTTP_USER_AGENT} (semrush|MJ12bot)
RewriteRule "^"  "-"  [F]
