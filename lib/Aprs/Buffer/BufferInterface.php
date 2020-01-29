<?php


namespace Aprs\Buffer;

/**
 * Interface BufferInterface
 * Used to store the data retrieved from the socket, and get messages from it. Can be a simple in-memory storage or
 * if a more robust solution is needed, maybe a caching database or filesystem solution can be implemented
 */
interface BufferInterface
{
    /**
     * Add raw data to the buffer
     *
     * @param int $length
     * @param string $buffer
     */
    public function addResult(int $length, string $buffer): void;

    /**
     * extract the first message from the buffer, and return it as a string.
     *
     * Returns null if no (complete) message is in the buffer
     *
     * @return string|null
     */
    public function getRawMessage(): ?string;
}