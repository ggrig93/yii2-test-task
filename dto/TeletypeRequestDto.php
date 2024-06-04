<?php

namespace app\dto;

class TeletypeRequestDto
{
    public string $name;
    public string $payload;
    public array $message;

    /**
     * TeletypeRequestDto constructor.
     *
     * @param string $name
     * @param string $payload
     * @param array $message
     */
    public function __construct(string $name, string $payload, array $message)
    {
        $this->name = $name;
        $this->payload = $payload;
        $this->message = $message;
    }
}
