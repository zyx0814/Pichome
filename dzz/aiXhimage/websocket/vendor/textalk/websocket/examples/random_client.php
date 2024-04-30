<?php

/**
 * Websocket client that read/write random data.
 * Run in console: php examples/random_client.php
 *
 * Console options:
 *  --uri <uri> : The URI to connect to, default ws://localhost:8000
 *  --timeout <int> : Timeout in seconds, random default
 *  --fragment_size <int> : Fragment size as bytes, random default
 *  --debug : Output log data (if logger is available)
 */

namespace WebSocket;

require __DIR__ . '/../vendor/autoload.php';

error_reporting(-1);

$randStr = function (int $maxlength = 4096) {
    $string = '';
    $length = rand(1, $maxlength);
    for ($i = 0; $i < $length; $i++) {
        $string .= chr(rand(33, 126));
    }
    return $string;
};

echo "> Random client\n";

// Server options specified or random
$options = array_merge([
    'uri'           => 'ws://localhost:8000',
    'timeout'       => rand(1, 60),
    'fragment_size' => rand(1, 4096) * 8,
], getopt('', ['uri:', 'timeout:', 'fragment_size:', 'debug']));

// If debug mode and logger is available
if (isset($options['debug']) && class_exists('WebSocket\EchoLog')) {
    $logger = new EchoLog();
    $options['logger'] = $logger;
    echo "> Using logger\n";
}

// Main loop
while (true) {
    try {
        $client = new Client($options['uri'], $options);
        $info = json_encode([
            'uri'           => $options['uri'],
            'timeout'       => $options['timeout'],
            'framgemt_size' => $client->getFragmentSize(),
        ]);
        echo "> Creating client {$info}\n";

        try {
            while (true) {
                // Random actions
                switch (rand(1, 10)) {
                    case 1:
                        echo "> Sending text\n";
                        $client->text("Text message {$randStr()}");
                        break;
                    case 2:
                        echo "> Sending binary\n";
                        $client->binary("Binary message {$randStr()}");
                        break;
                    case 3:
                        echo "> Sending close\n";
                        $client->close(rand(1000, 2000), "Close message {$randStr(8)}");
                        break;
                    case 4:
                        echo "> Sending ping\n";
                        $client->ping("Ping message  {$randStr(8)}");
                        break;
                    case 5:
                        echo "> Sending pong\n";
                        $client->pong("Pong message  {$randStr(8)}");
                        break;
                    default:
                        echo "> Receiving\n";
                        $received = $client->receive();
                        echo "> Received {$client->getLastOpcode()}: {$received}\n";
                }
                sleep(rand(1, 5));
            }
        } catch (\Throwable $e) {
            echo "ERROR I/O: {$e->getMessage()} [{$e->getCode()}]\n";
        }
    } catch (\Throwable $e) {
        echo "ERROR: {$e->getMessage()} [{$e->getCode()}]\n";
    }
    sleep(rand(1, 5));
}
