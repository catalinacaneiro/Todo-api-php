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

if ($method === 'PATCH' && preg_match('#^/tasks/([1-9][0-9]*)$#', $path, $matches)) {
    $id = $matches[1];
    $data = json_decode(file_get_contents('php://input'));

    if (json_last_error() !== JSON_ERROR_NONE || !($data instanceof stdClass)) {
        http_response_code(400);
        echo json_encode(['error' => 'Request body must be a valid JSON object']);
        exit;
    }

    if (!property_exists($data, 'title') && !property_exists($data, 'description') && !property_exists($data, 'completed')) {
        http_response_code(400);
        echo json_encode(['error' => 'At least one of title, description, or completed must be supplied']);
        exit;
    }

    foreach ($data as $field => $value) {
        if (!in_array($field, ['title', 'description', 'completed'], true)) {
            http_response_code(400);
            echo json_encode(['error' => 'Only title, description, and completed may be updated']);
            exit;
        }
    }

    if (property_exists($data, 'title') && (!is_string($data->title) || trim($data->title) === '')) {
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

    $updates = [];
    $parameters = ['id' => $id];

    if (property_exists($data, 'title')) {
        $updates[] = 'title = :title';
        $parameters['title'] = trim($data->title);
    }

    if (property_exists($data, 'description')) {
        $updates[] = 'description = :description';
        $parameters['description'] = $data->description;
    }

    if (property_exists($data, 'completed')) {
        $updates[] = 'completed = :completed';
        $parameters['completed'] = (int) $data->completed;
    }

    try {
        $pdo = require __DIR__ . '/../src/Database.php';
        $statement = $pdo->prepare('SELECT * FROM task WHERE id = :id');
        $statement->execute(['id' => $id]);
        $task = $statement->fetch(PDO::FETCH_ASSOC);

        if ($task === false) {
            http_response_code(404);
            echo json_encode(['error' => 'Task not found']);
            exit;
        }

        $statement = $pdo->prepare('UPDATE task SET ' . implode(', ', $updates) . ' WHERE id = :id');
        $statement->execute($parameters);

        $statement = $pdo->prepare('SELECT * FROM task WHERE id = :id');
        $statement->execute(['id' => $id]);
        $task = $statement->fetch(PDO::FETCH_ASSOC);

        http_response_code(200);
        echo json_encode($task);
    } catch (PDOException $exception) {
        http_response_code(500);
        echo json_encode(['error' => 'Could not update task']);
    }

    exit;
}

if ($method === 'DELETE' && preg_match('#^/tasks/([1-9][0-9]*)$#', $path, $matches)) {
    $id = $matches[1];

    try {
        $pdo = require __DIR__ . '/../src/Database.php';
        $statement = $pdo->prepare('DELETE FROM task WHERE id = :id');
        $statement->execute(['id' => $id]);

        if ($statement->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Task not found']);
            exit;
        }

        http_response_code(204);
    } catch (PDOException $exception) {
        http_response_code(500);
        echo json_encode(['error' => 'Could not delete task']);
    }

    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Not found']);
