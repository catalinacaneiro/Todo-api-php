<?php

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

header('Content-Type: application/json; charset=utf-8');

if ($method === 'GET' && $path === '/tasks') {
    $pdo = require __DIR__ . '/../src/Database.php';
    $statement = $pdo->query('SELECT * FROM task');
    $tasks = $statement->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($tasks);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Not found']);
