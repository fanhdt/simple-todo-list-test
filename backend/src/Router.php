<?php
class Router
{
    private TodoRepository $repository;

    public function __construct(TodoRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handle(string $method, string $uri): void
    {
        $this->applyCorsHeaders();

        // Preflight request dari browser
        if ($method === 'OPTIONS') {
            http_response_code(204);
            return;
        }

        header('Content-Type: application/json');

        $path = parse_url($uri, PHP_URL_PATH) ?? '/';
        $path = rtrim($path, '/');
        if ($path === '') {
            $path = '/';
        }

        try {
            // GET /health
            if ($method === 'GET' && $path === '/health') {
                $this->sendJson(200, ['data' => ['status' => 'ok']]);
                return;
            }

            // GET /api/todos
            if ($method === 'GET' && $path === '/api/todos') {
                $todos = $this->repository->findAll();
                $this->sendJson(200, ['data' => array_map([$this, 'formatTodo'], $todos)]);
                return;
            }

            // GET /api/todos/{id}
            if ($method === 'GET' && preg_match('#^/api/todos/(\d+)$#', $path, $m)) {
                $todo = $this->repository->findById((int) $m[1]);
                if ($todo === null) {
                    $this->sendError(404, 'Todo not found');
                    return;
                }
                $this->sendJson(200, ['data' => $this->formatTodo($todo)]);
                return;
            }

            // POST /api/todos
            if ($method === 'POST' && $path === '/api/todos') {
                $this->handleCreate();
                return;
            }

            // PATCH /api/todos/{id}
            if ($method === 'PATCH' && preg_match('#^/api/todos/(\d+)$#', $path, $m)) {
                $this->handleUpdate((int) $m[1]);
                return;
            }

            // DELETE /api/todos/{id}
            if ($method === 'DELETE' && preg_match('#^/api/todos/(\d+)$#', $path, $m)) {
                $deleted = $this->repository->delete((int) $m[1]);
                if (!$deleted) {
                    $this->sendError(404, 'Todo not found');
                    return;
                }
                http_response_code(204);
                return;
            }

            // Route tidak dikenal
            if (preg_match('#^/api/todos(/\d+)?$#', $path)) {
                $this->sendError(405, 'Method not allowed');
                return;
            }

            $this->sendError(404, 'Route not found');
        } catch (Throwable $e) {
            // Detail error dicatat di server log, tidak dikirim ke client
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendError(500, 'Internal server error');
        }
    }

    private function handleCreate(): void
    {
        $body = $this->readJsonBody();

        if ($body === null) {
            $this->sendError(400, 'Invalid JSON body');
            return;
        }

        if (!array_key_exists('title', $body)) {
            $this->sendError(400, 'Field "title" is required');
            return;
        }

        $title = $body['title'];

        if (!is_string($title)) {
            $this->sendError(400, 'Field "title" must be a string');
            return;
        }

        $title = trim($title);

        if ($title === '') {
            $this->sendError(400, 'Field "title" must not be empty');
            return;
        }

        if (mb_strlen($title) > 255) {
            $this->sendError(400, 'Field "title" must be at most 255 characters');
            return;
        }

        $todo = $this->repository->create($title);
        $this->sendJson(201, ['data' => $this->formatTodo($todo)]);
    }

    private function handleUpdate(int $id): void
    {
        $body = $this->readJsonBody();

        if ($body === null) {
            $this->sendError(400, 'Invalid JSON body');
            return;
        }

        $title     = null;
        $completed = null;

        if (array_key_exists('title', $body)) {
            if (!is_string($body['title'])) {
                $this->sendError(400, 'Field "title" must be a string');
                return;
            }
            $title = trim($body['title']);

            if ($title === '') {
                $this->sendError(400, 'Field "title" must not be empty');
                return;
            }
            if (mb_strlen($title) > 255) {
                $this->sendError(400, 'Field "title" must be at most 255 characters');
                return;
            }
        }

        if (array_key_exists('completed', $body)) {
            if (!is_bool($body['completed'])) {
                $this->sendError(400, 'Field "completed" must be a boolean');
                return;
            }
            $completed = $body['completed'];
        }

        if ($title === null && $completed === null) {
            $this->sendError(400, 'At least one of "title" or "completed" must be provided');
            return;
        }

        $todo = $this->repository->update($id, $title, $completed);

        if ($todo === null) {
            $this->sendError(404, 'Todo not found');
            return;
        }

        $this->sendJson(200, ['data' => $this->formatTodo($todo)]);
    }

    /**
     * Membaca dan mem-parsing JSON body dari request.
     * Mengembalikan null jika JSON tidak valid.
     */
    private function readJsonBody(): ?array
    {
        $raw = file_get_contents('php://input');

        if ($raw === '' || $raw === false) {
            return [];
        }

        $decoded = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    private function formatTodo(array $todo): array
    {
        return [
            'id'         => (int) $todo['id'],
            'title'      => $todo['title'],
            'completed'  => is_bool($todo['completed']) ? $todo['completed'] : ($todo['completed'] === 't' || $todo['completed'] === true),
            'created_at' => $todo['created_at'],
            'updated_at' => $todo['updated_at'],
        ];
    }

    private function sendJson(int $status, array $payload): void
    {
        http_response_code($status);
        echo json_encode($payload);
    }

    private function sendError(int $status, string $message): void
    {
        $this->sendJson($status, ['error' => ['message' => $message]]);
    }

    /**
     * CORS sederhana untuk keperluan pembelajaran.
     * Mengizinkan semua origin (*) agar frontend dapat diakses dari mana saja
     * saat development maupun setelah dideploy secara terpisah.
     */
    private function applyCorsHeaders(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
    }
}
