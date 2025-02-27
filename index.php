<?php

use Viewi\Components\Http\Message\Response;

require __DIR__ . '/vendor/autoload.php';

// Viewi application here
/**
 * @var Viewi\App $app
 */
$app = include __DIR__ . '/test/viewi.php';

// Viewi components
include __DIR__ . '/test/routes.php';

$response = $app->run();

if (is_string($response)) {
    header("Content-type: text/html; charset=utf-8");
    echo $response;
} elseif ($response instanceof Response) {
    http_response_code($response->status);
    foreach ($response->headers as $name => $value) {
        header("$name: $value");
    }
    echo $response->body;
} else {
    header("Content-type: application/json; charset=utf-8");
    echo json_encode($response);
}
