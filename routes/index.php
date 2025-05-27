<?php
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
// require_once __DIR__ . '/../controllers/DepartmentController.php';
// require_once __DIR__ . '/../controllers/TicketController.php';

header('Content-Type: application/json');

$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

switch (true) {
    case $uri === '/register' && $method === 'POST':
        (new UserController())->register();
        break;
    case $uri === '/login' && $method === 'POST':
        (new UserController())->login();
        break;
     case $uri === '/logout' && $method === 'POST':
        $user = AuthMiddleware::checkAuth(); 
        (new UserController())->logout($user);
        break;


    default:
        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
}
