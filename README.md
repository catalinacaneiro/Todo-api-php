# Todo API – PHP

A simple REST API for managing todo tasks.
Built with PHP, PDO and MySQL.

## Features

The API supports basic CRUD operations:

- `GET /tasks` – List all tasks
- `POST /tasks` – Create a new task
- `PATCH /tasks/{id}` – Update an existing task
- `DELETE /tasks/{id}` – Delete a task

## Task

A task contains:

- `id`
- `title`
- `description`
- `completed`
- `created_at`

## Project structure

- `public/index.php` – Handles the API routes and requests
- `src/Database.php` – Creates the PDO database connection
- `frontend/` – Optional Svelte and Tailwind CSS frontend

## Technologies

- PHP
- MySQL
- PDO
- Svelte
- Tailwind CSS

## Running the API

Start the PHP development server from the project root:

```bash
php -S localhost:8000 -t public
```

The API is then available at:

```text
http://localhost:8000
```

## Frontend

The optional frontend is located in the `frontend` directory.

```bash
cd frontend
npm install
npm run dev
```