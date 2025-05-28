<?php
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../controllers/DepartmentController.php';
require_once __DIR__ . '/../controllers/TicketController.php';
require_once __DIR__ . '/../controllers/NoteController.php';
require_once __DIR__ . '/../middlewares/RateLimiterMiddleware.php'; 

header('Content-Type: application/json');

$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

switch (true) {
    // user
    case $uri === '/api/register' && $method === 'POST':
        (new UserController())->register();
        break;
    case $uri === '/api/login' && $method === 'POST':
        (new UserController())->login();
        break;
    case $uri === '/api/logout' && $method === 'POST':
        $user = AuthMiddleware::checkAuth();
        (new UserController())->logout($user);
        break;

    // department
    case $uri === '/api/department' && $method === 'POST':
        $user = AuthMiddleware::checkAdmin();
        (new DepartmentController())->create();
        break;

    case preg_match('#^/api/department/(\d+)$#', $uri, $matches) && $method === 'PUT':
        $user = AuthMiddleware::checkAdmin();
        $id = $matches[1];
        (new DepartmentController())->update($id);
        break;
    case preg_match('#^/api/department/(\d+)$#', $uri, $matches) && $method === 'DELETE':
        $user = AuthMiddleware::checkAdmin();
        $id = $matches[1];
        (new DepartmentController())->delete($id);
        break;

    // ticket
    case $uri === '/api/tickets' && $method === 'POST':
        RateLimiterMiddleware::check(1); 
        $user = AuthMiddleware::checkAuth();
        (new TicketController())->create($user);
        break;

    case preg_match('#^/api/tickets/(\d+)/assign$#', $uri, $matches) && $method === 'POST':
        $id = $matches[1];
        $user = AuthMiddleware::checkAuth();
        (new TicketController())->assignToSelf($id, $user);
        break;
    case preg_match('#^/api/tickets/(\d+)/status$#', $uri, $matches) && $method === 'PUT':
        $id = $matches[1];
        $user = AuthMiddleware::checkAuth();
        (new TicketController())->updateStatus($id, $user);
        break;
    case preg_match('#^/api/tickets/(\d+)/notes$#', $uri, $matches) && $method === 'POST':
        $ticketId = $matches[1];
        $user = AuthMiddleware::checkAuth();
        (new NoteController())->addNote($ticketId, $user);
        break;




    default:
        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
}
