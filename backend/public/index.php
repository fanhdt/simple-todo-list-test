<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/TodoRepository.php';
require_once __DIR__ . '/../src/Router.php';

$pdo        = Database::getConnection();
$repository = new TodoRepository($pdo);
$router     = new Router($repository);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri    = $_SERVER['REQUEST_URI'] ?? '/';

$router->handle($method, $uri);
