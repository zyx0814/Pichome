<?php

/**
 * Copyright (C) 2014-2022 Textalk/Abicart and contributors.
 *
 * This file is part of Websocket PHP and is free software under the ISC License.
 * License text: https://raw.githubusercontent.com/Textalk/websocket-php/master/COPYING
 */

namespace WebSocket;

use Psr\Log\{
    LoggerAwareInterface,
    LoggerAwareTrait,
    LoggerInterface, NullLogger
};
use WebSocket\Message\{
    Factory,
    Message
};

class Connection implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    use OpcodeTrait;

    private $stream;
    private $read_buffer;
    private $msg_factory;
    private $options = [];

    protected $is_closing = false;
    protected $close_status = null;

    private $uid;

    /* ---------- Construct & Destruct ----------------------------------------------- */

    public function __construct($stream, array $options = [])
    {
        $this->stream = $stream;
        $this->setOptions($options);
        $this->setLogger(new NullLogger());
        $this->msg_factory = new Factory();
    }

    public function __destruct()
    {
        if ($this->getType() === 'stream') {
            fclose($this->stream);
        }
    }

    public function setOptions(array $options = []): void
    {
        $this->options = array_merge($this->options, $options);
    }

    public function getCloseStatus(): ?int
    {
        return $this->close_status;
    }

    /**
     * Tell the socket to close.
     *
     * @param integer $status  http://tools.ietf.org/html/rfc6455#section-7.4
     * @param string  $message A closing message, max 125 bytes.
     */
    public function close(int $status = 1000, string $message = 'ttfn'): void
    {
        if (!$this->isConnected()) {
            return;
        }
        $status_binstr = sprintf('%016b', $status);
        $status_str = '';
        foreach (str_split($status_binstr, 8) as $binstr) {
            $status_str .= chr(bindec($binstr));
        }
        $message = $this->msg_factory->create('close', $status_str . $message);
        $this->pushMessage($message, true);

        $this->logger->debug("Closing with status: {$status}.");

        $this->is_closing = true;
        while (true) {
            $message = $this->pullMessage();
            if ($message->getOpcode() == 'close') {
                break;
            }
        }
    }


    /* ---------- Message methods ---------------------------------------------------- */

    // Push a message to stream
    public function pushMessage(Message $message, bool $masked = true): void
    {
        $frames = $message->getFrames($masked, $this->options['fragment_size']);
        foreach ($frames as $frame) {
            $this->pushFrame($frame);
        }
        $this->logger->info("[connection] Pushed {$message}", [
            'opcode' => $message->getOpcode(),
            'content-length' => $message->getLength(),
            'frames' => count($frames),
        ]);
    }

    // Pull a message from stream
    public function pullMessage(): Message
    {
        do {
            $frame = $this->pullFrame();
            $frame = $this->autoRespond($frame);
            list ($final, $payload, $opcode, $masked) = $frame;

            if ($opcode == 'close') {
                $this->close();
            }

            // Continuation and factual opcode
            $continuation = $opcode == 'continuation';
            $payload_opcode = $continuation ? $this->read_buffer['opcode'] : $opcode;

            // First continuation frame, create buffer
            if (!$final && !$continuation) {
                $this->read_buffer = ['opcode' => $opcode, 'payload' => $payload, 'frames' => 1];
                continue; // Continue reading
            }

            // Subsequent continuation frames, add to buffer
            if ($continuation) {
                $this->read_buffer['payload'] .= $payload;
                $this->read_buffer['frames']++;
            }
        } while (!$final);

        // Final, return payload
        $frames = 1;
        if ($continuation) {
            $payload = $this->read_buffer['payload'];
            $frames = $this->read_buffer['frames'];
            $this->read_buffer = null;
        }

        $message = $this->msg_factory->create($payload_opcode, $payload);

        $this->logger->info("[connection] Pulled {$message}", [
            'opcode' => $payload_opcode,
            'content-length' => strlen($payload),
            'frames' => $frames,
        ]);

        return $message;
    }


    /* ---------- Frame I/O methods -------------------------------------------------- */

    // Pull frame from stream
    private function pullFrame(): array
    {
        // Read the fragment "header" first, two bytes.
        $data = $this->read(2);
        list ($byte_1, $byte_2) = array_values(unpack('C*', $data));
        $final = (bool)($byte_1 & 0b10000000); // Final fragment marker.
        $rsv = $byte_1 & 0b01110000; // Unused bits, ignore

        // Parse opcode
        $opcode_int = $byte_1 & 0b00001111;
        $opcode_ints = array_flip(self::$opcodes);
        if (!array_key_exists($opcode_int, $opcode_ints)) {
            $warning = "Bad opcode in websocket frame: {$opcode_int}";
            $this->logger->warning($warning);
            throw new ConnectionException($warning, ConnectionException::BAD_OPCODE);
        }
        $opcode = $opcode_ints[$opcode_int];

        // Masking bit
        $masked = (bool)($byte_2 & 0b10000000);

        $payload = '';

        // Payload length
        $payload_length = $byte_2 & 0b01111111;

        if ($payload_length > 125) {
            if ($payload_length === 126) {
                $data = $this->read(2); // 126: Payload is a 16-bit unsigned int
                $payload_length = current(unpack('n', $data));
            } else {
                $data = $this->read(8); // 127: Payload is a 64-bit unsigned int
                $payload_length = current(unpack('J', $data));
            }
        }

        // Get masking key.
        if ($masked) {
            $masking_key = $this->read(4);
        }

        // Get the actual payload, if any (might not be for e.g. close frames.
        if ($payload_length > 0) {
            $data = $this->read($payload_length);

            if ($masked) {
                // Unmask payload.
                for ($i = 0; $i < $payload_length; $i++) {
                    $payload .= ($data[$i] ^ $masking_key[$i % 4]);
                }
            } else {
                $payload = $data;
            }
        }

        $this->logger->debug("[connection] Pulled '{opcode}' frame", [
            'opcode' => $opcode,
            'final' => $final,
            'content-length' => strlen($payload),
        ]);
        return [$final, $payload, $opcode, $masked];
    }

    // Push frame to stream
    private function pushFrame(array $frame): void
    {
        list ($final, $payload, $opcode, $masked) = $frame;
        $data = '';
        $byte_1 = $final ? 0b10000000 : 0b00000000; // Final fragment marker.
        $byte_1 |= self::$opcodes[$opcode]; // Set opcode.
        $data .= pack('C', $byte_1);

        $byte_2 = $masked ? 0b10000000 : 0b00000000; // Masking bit marker.

        // 7 bits of payload length...
        $payload_length = strlen($payload);
        if ($payload_length > 65535) {
            $data .= pack('C', $byte_2 | 0b01111111);
            $data .= pack('J', $payload_length);
        } elseif ($payload_length > 125) {
            $data .= pack('C', $byte_2 | 0b01111110);
            $data .= pack('n', $payload_length);
        } else {
            $data .= pack('C', $byte_2 | $payload_length);
        }

        // Handle masking
        if ($masked) {
            // generate a random mask:
            $mask = '';
            for ($i = 0; $i < 4; $i++) {
                $mask .= chr(rand(0, 255));
            }
            $data .= $mask;

            // Append payload to frame:
            for ($i = 0; $i < $payload_length; $i++) {
                $data .= $payload[$i] ^ $mask[$i % 4];
            }
        } else {
            $data .= $payload;
        }

        $this->write($data);

        $this->logger->debug("[connection] Pushed '{$opcode}' frame", [
            'opcode' => $opcode,
            'final' => $final,
            'content-length' => strlen($payload),
        ]);
    }

    // Trigger auto response for frame
    private function autoRespond(array $frame)
    {
        list ($final, $payload, $opcode, $masked) = $frame;
        $payload_length = strlen($payload);

        switch ($opcode) {
            case 'ping':
                // If we received a ping, respond with a pong
                $this->logger->debug("[connection] Received 'ping', sending 'pong'.");
                $message = $this->msg_factory->create('pong', $payload);
                $this->pushMessage($message, $masked);
                return [$final, $payload, $opcode, $masked];
            case 'close':
                // If we received close, possibly acknowledge and close connection
                $status_bin = '';
                $status = '';
                if ($payload_length > 0) {
                    $status_bin = $payload[0] . $payload[1];
                    $status = current(unpack('n', $payload));
                    $this->close_status = $status;
                }
                // Get additional close message
                if ($payload_length >= 2) {
                    $payload = substr($payload, 2);
                }

                $this->logger->debug("[connection] Received 'close', status: {$status}.");
                if (!$this->is_closing) {
                    $ack =  "{$status_bin}Close acknowledged: {$status}";
                    $message = $this->msg_factory->create('close', $ack);
                    $this->pushMessage($message, $masked);
                } else {
                    $this->is_closing = false; // A close response, all done.
                }
                $this->disconnect();
                return [$final, $payload, $opcode, $masked];
            default:
                return [$final, $payload, $opcode, $masked];
        }
    }


    /* ---------- Stream I/O methods ------------------------------------------------- */

    /**
     * Close connection stream.
     * @return bool
     */
    public function disconnect(): bool
    {
        $this->logger->debug('Closing connection');
        return fclose($this->stream);
    }

    /**
     * If connected to stream.
     * @return bool
     */
    public function isConnected(): bool
    {
        return in_array($this->getType(), ['stream', 'persistent stream']);
    }

    /**
     * Return type of connection.
     * @return string|null Type of connection or null if invalid type.
     */
    public function getType(): ?string
    {
        return get_resource_type($this->stream);
    }

    /**
     * Get name of local socket, or null if not connected.
     * @return string|null
     */
    public function getName(): ?string
    {
        return stream_socket_get_name($this->stream, false);
    }

    /**
     * Get name of remote socket, or null if not connected.
     * @return string|null
     */
    public function getRemoteName(): ?string
    {
        return stream_socket_get_name($this->stream, true);
    }

    /**
     * Get meta data for connection.
     * @return array
     */
    public function getMeta(): array
    {
        return stream_get_meta_data($this->stream);
    }

    /**
     * Returns current position of stream pointer.
     * @return int
     * @throws ConnectionException
     */
    public function tell(): int
    {
        $tell = ftell($this->stream);
        if ($tell === false) {
            $this->throwException('Could not resolve stream pointer position');
        }
        return $tell;
    }

    /**
     * If stream pointer is at end of file.
     * @return bool
     */
    public function eof(): int
    {
        return feof($this->stream);
    }


    /* ---------- Stream option methods ---------------------------------------------- */

    /**
     * Set time out on connection.
     * @param int $seconds Timeout part in seconds
     * @param int $microseconds Timeout part in microseconds
     * @return bool
     */
    public function setTimeout(int $seconds, int $microseconds = 0): bool
    {
        $this->logger->debug("Setting timeout {$seconds}:{$microseconds} seconds");
        return stream_set_timeout($this->stream, $seconds, $microseconds);
    }


    /* ---------- Stream read/write methods ------------------------------------------ */

    /**
     * Read line from stream.
     * @param int $length Maximum number of bytes to read
     * @param string $ending Line delimiter
     * @return string Read data
     */
    public function getLine(int $length, string $ending): string
    {
        $line = stream_get_line($this->stream, $length, $ending);
        if ($line === false) {
            $this->throwException('Could not read from stream');
        }
        $read = strlen($line);
        $this->logger->debug("Read {$read} bytes of line.");
        return $line;
    }

    /**
     * Read line from stream.
     * @param int $length Maximum number of bytes to read
     * @return string Read data
     */
    public function gets(int $length): string
    {
        $line = fgets($this->stream, $length);
        if ($line === false) {
            $this->throwException('Could not read from stream');
        }
        $read = strlen($line);
        $this->logger->debug("Read {$read} bytes of line.");
        return $line;
    }

    /**
     * Read characters from stream.
     * @param int $length Maximum number of bytes to read
     * @return string Read data
     */
    public function read(string $length): string
    {
        $data = '';
        while (strlen($data) < $length) {
            $buffer = fread($this->stream, $length - strlen($data));
            if (!$buffer) {
                $meta = stream_get_meta_data($this->stream);
                if (!empty($meta['timed_out'])) {
                    $message = 'Client read timeout';
                    $this->logger->error($message, $meta);
                    throw new TimeoutException($message, ConnectionException::TIMED_OUT, $meta);
                }
            }
            if ($buffer === false) {
                $read = strlen($data);
                $this->throwException("Broken frame, read {$read} of stated {$length} bytes.");
            }
            if ($buffer === '') {
                $this->throwException("Empty read; connection dead?");
            }
            $data .= $buffer;
            $read = strlen($data);
            $this->logger->debug("Read {$read} of {$length} bytes.");
        }
        return $data;
    }

    /**
     * Write characters to stream.
     * @param string $data Data to read
     */
    public function write(string $data): void
    {
        $length = strlen($data);
        $written = fwrite($this->stream, $data);
        if ($written === false) {
            $this->throwException("Failed to write {$length} bytes.");
        }
        if ($written < strlen($data)) {
            $this->throwException("Could only write {$written} out of {$length} bytes.");
        }
        $this->logger->debug("Wrote {$written} of {$length} bytes.");
    }


    /* ---------- Internal helper methods -------------------------------------------- */

    private function throwException(string $message, int $code = 0): void
    {
        $meta = ['closed' => true];
        if ($this->isConnected()) {
            $meta = $this->getMeta();
            $this->disconnect();
            if (!empty($meta['timed_out'])) {
                $this->logger->error($message, $meta);
                throw new TimeoutException($message, ConnectionException::TIMED_OUT, $meta);
            }
            if (!empty($meta['eof'])) {
                $code = ConnectionException::EOF;
            }
        }
        $this->logger->error($message, $meta);
        throw new ConnectionException($message, $code, $meta);
    }
}
