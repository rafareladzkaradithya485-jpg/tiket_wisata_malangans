<?php
header('Content-Type: application/json');

$port = getenv('PORT') ?: getenv('SERVER_PORT') ?: 8000;

echo json_encode([
    'status' => 'ok',
    'php_version' => phpversion(),
    'port' => (int)$port,
]);

?>
