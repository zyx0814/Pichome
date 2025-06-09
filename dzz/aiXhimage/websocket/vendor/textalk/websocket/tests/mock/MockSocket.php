<?php

/**
 * This class is used by tests to mock and track various socket/stream calls.
 */

namespace WebSocket;

class MockSocket
{
    private static $queue = [];
    private static $stored = [];
    private static $asserter;

    // Handler called by function overloads in mock-socket.php
    public static function handle($function, $params = [])
    {
        $current = array_shift(self::$queue);
        if ($function == 'get_resource_type' && is_null($current)) {
            return null; // Catch destructors
        }
        self::$asserter->assertEquals($current['function'], $function);
        foreach ($current['params'] as $index => $param) {
            if (isset($current['input-op'])) {
                $param = self::op($current['input-op'], $params, $param);
            }
            self::$asserter->assertEquals($param, $params[$index], json_encode([$current, $params]));
        }
        if (isset($current['error'])) {
            $map = array_merge(['msg' => 'Error', 'type' => E_USER_NOTICE], (array)$current['error']);
            trigger_error($map['msg'], $map['type']);
        }
        if (isset($current['return-op'])) {
            return self::op($current['return-op'], $params, $current['return']);
        }
        if (isset($current['return'])) {
            return $current['return'];
        }
        return call_user_func_array($function, $params);
    }

    // Check if all expected calls are performed
    public static function isEmpty(): bool
    {
        return empty(self::$queue);
    }

    // Initialize call queue
    public static function initialize($op_file, $asserter): void
    {
        $file = dirname(__DIR__) . "/scripts/{$op_file}.json";
        self::$queue = json_decode(file_get_contents($file), true);
        self::$asserter = $asserter;
    }

    // Special output handling
    private static function op($op, $params, $data)
    {
        switch ($op) {
            case 'chr-array':
                // Convert int array to string
                $out = '';
                foreach ($data as $val) {
                    $out .= chr($val);
                }
                return $out;
            case 'file':
                $content = file_get_contents(__DIR__ . "/{$data[0]}");
                return substr($content, $data[1], $data[2]);
            case 'key-save':
                preg_match('#Sec-WebSocket-Key:\s(.*)$#mUi', $params[1], $matches);
                self::$stored['sec-websocket-key'] = trim($matches[1]);
                return str_replace('{key}', self::$stored['sec-websocket-key'], $data);
            case 'key-respond':
                $key = self::$stored['sec-websocket-key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
                $encoded = base64_encode(pack('H*', sha1($key)));
                return str_replace('{key}', $encoded, $data);
        }
        return $data;
    }
}
