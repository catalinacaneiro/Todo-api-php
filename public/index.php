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

if ($method === 'POST' && $path === '/tasks') {
    $data = json_decode(file_get_contents('php://input'));

    if (json_last_error() !== JSON_ERROR_NONE || !($data instanceof stdClass)) {
        http_response_code(400);
        echo json_encode(['error' => 'Request body must be a valid JSON object']);
        exit;
    }

    if (!isset($data->title) || !is_string($data->title) || trim($data->title) === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Title must be a non-empty string']);
        exit;
    }

    if (isset($data->description) && !is_string($data->description)) {
        http_response_code(400);
        echo json_encode(['error' => 'Description must be a string or null']);
        exit;
    }

    if (property_exists($data, 'completed') && !is_bool($data->completed)) {
        http_response_code(400);
        echo json_encode(['error' => 'Completed must be a boolean']);
        exit;
    }

    try {
        $pdo = require __DIR__ . '/../src/Database.php';
        $statement = $pdo->prepare(
            'INSERT INTO task (title, description, completed) VALUES (:title, :description, :completed)'
        );
        $statement->execute([
            'title' => trim($data->title),
            'description' => $data->description ?? null,
            'completed' => (int) ($data->completed ?? false),
        ]);

        $id = $pdo->lastInsertId();
        $statement = $pdo->prepare('SELECT * FROM task WHERE id = :id');
        $statement->execute(['id' => $id]);
        $task = $statement->fetch(PDO::FETCH_ASSOC);

        http_response_code(201);
        echo json_encode($task);
    } catch (PDOException $exception) {
        http_response_code(500);
        echo json_encode(['error' => 'Could not create task']);
    }

    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Not found']);
