<?php

/**
 * Websocket server that read/write random data.
 * Run in console: php examples/random_server.php
 *
 * Console options:
 *  --port <int> : The port to listen to, default 8000
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

echo "> Random server\n";

// Server options specified or random
$options = array_merge([
    'port'          => 8000,
    'timeout'       => rand(1, 60),
    'fragment_size' => rand(1, 4096) * 8,
], getopt('', ['port:', 'timeout:', 'fragment_size:', 'debug']));

// If debug mode and logger is available
if (isset($options['debug']) && class_exists('WebSocket\EchoLog')) {
    $logger = new EchoLog();
    $options['logger'] = $logger;
    echo "> Using logger\n";
}

// Force quit to close server
while (true) {
    try {
        // Setup server
        $server = new Server($options);
        $info = json_encode([
          'port'          => $server->getPort(),
          'timeout'       => $options['timeout'],
          'framgemt_size' => $server->getFragmentSize(),
        ]);
        echo "> Creating server {$info}\n";

        while ($server->accept()) {
            while (true) {
                // Random actions
                switch (rand(1, 10)) {
                    case 1:
                        echo "> Sending text\n";
                        $server->text("Text message {$randStr()}");
                        break;
                    case 2:
                        echo "> Sending binary\n";
                        $server->binary("Binary message {$randStr()}");
                        break;
                    case 3:
                        echo "> Sending close\n";
                        $server->close(rand(1000, 2000), "Close message {$randStr(8)}");
                        break;
                    case 4:
                        echo "> Sending ping\n";
                        $server->ping("Ping message {$randStr(8)}");
                        break;
                    case 5:
                        echo "> Sending pong\n";
                        $server->pong("Pong message {$randStr(8)}");
                        break;
                    default:
                        echo "> Receiving\n";
                        $received = $server->receive();
                        echo "> Received {$server->getLastOpcode()}: {$received}\n";
                }
                sleep(rand(1, 5));
            }
        }
    } catch (\Throwable $e) {
        echo "ERROR: {$e->getMessage()} [{$e->getCode()}]\n";
    }
    sleep(rand(1, 5));
}
