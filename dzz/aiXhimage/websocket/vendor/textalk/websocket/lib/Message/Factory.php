<?php

/**
 * Copyright (C) 2014-2022 Textalk/Abicart and contributors.
 *
 * This file is part of Websocket PHP and is free software under the ISC License.
 * License text: https://raw.githubusercontent.com/Textalk/websocket-php/master/COPYING
 */

namespace WebSocket\Message;

use WebSocket\BadOpcodeException;

class Factory
{
    public function create(string $opcode, string $payload = ''): Message
    {
        switch ($opcode) {
            case 'text':
                return new Text($payload);
            case 'binary':
                return new Binary($payload);
            case 'ping':
                return new Ping($payload);
            case 'pong':
                return new Pong($payload);
            case 'close':
                return new Close($payload);
        }
        throw new BadOpcodeException("Invalid opcode '{$opcode}' provided");
    }
}
