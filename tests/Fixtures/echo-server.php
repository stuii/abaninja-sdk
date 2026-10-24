<?php

declare(strict_types=1);

/*
 * Router for PHP's built-in web server: answers every request with a JSON description of it.
 * The response status can be set with the `status` query parameter, `?raw=...` returns the value as body.
 */

if (isset($_GET['raw'])) {
    echo $_GET['raw'];
    return;
}

http_response_code((int) ($_GET['status'] ?? 200));
header('Content-Type: application/json');

echo json_encode([
    'method' => $_SERVER['REQUEST_METHOD'],
    'uri' => $_SERVER['REQUEST_URI'],
    'headers' => getallheaders(),
    'body' => file_get_contents('php://input'),
]);
