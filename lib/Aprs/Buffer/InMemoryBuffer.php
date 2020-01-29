<?php


namespace Aprs\Buffer;


class InMemoryBuffer implements BufferInterface
{

    /**
     * @var int
     */
    private $length = 0;
    /**
     * @var string
     */
    private $buffer = '';

    public function addResult(int $length, string $buffer): void
    {
        $this->length += $length;
        $this->buffer .= $buffer;
    }

    public function getRawMessage(): ?string
    {
        $offset = strpos($this->buffer, "\n");
        if ($offset === false) {
            return null;
        }

        $segment = substr($this->buffer, 0, $offset);
        $this->buffer = substr($this->buffer, $offset + 1);
        $this->length -= $offset;
        return $segment;
    }
}