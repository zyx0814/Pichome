<?php

/**
 * Copyright (C) 2014-2022 Textalk/Abicart and contributors.
 *
 * This file is part of Websocket PHP and is free software under the ISC License.
 * License text: https://raw.githubusercontent.com/Textalk/websocket-php/master/COPYING
 */

namespace WebSocket\Message;

use DateTime;

abstract class Message
{
    protected $opcode;
    protected $payload;
    protected $timestamp;

    public function __construct(string $payload = '')
    {
        $this->payload = $payload;
        $this->timestamp = new DateTime();
    }

    public function getOpcode(): string
    {
        return $this->opcode;
    }

    public function getLength(): int
    {
        return strlen($this->payload);
    }

    public function getTimestamp(): DateTime
    {
        return $this->timestamp;
    }

    public function getContent(): string
    {
        return $this->payload;
    }

    public function setContent(string $payload = ''): void
    {
        $this->payload = $payload;
    }

    public function hasContent(): bool
    {
        return $this->payload != '';
    }

    public function __toString(): string
    {
        return get_class($this);
    }

    // Split messages into frames
    public function getFrames(bool $masked = true, int $framesize = 4096): array
    {

        $frames = [];
        $split = str_split($this->getContent(), $framesize) ?: [''];
        foreach ($split as $payload) {
            $frames[] = [false, $payload, 'continuation', $masked];
        }
        $frames[0][2] = $this->opcode;
        $frames[$this->array_key_last_r($frames)][0] = true;
        return $frames;
    }
    public function array_key_last_r($array)
    {
        if (!function_exists('array_key_last')) {

            return key(array_slice($array, -1, 1, true));

        }else{
            return array_key_last($array);
        }
    }
}
