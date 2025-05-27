<?php
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../controllers/DepartmentController.php';
// require_once __DIR__ . '/../controllers/TicketController.php';

header('Content-Type: application/json');

$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

switch (true) {
    // user
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

    // department
    case $uri === '/department' && $method === 'POST':
        $user = AuthMiddleware::checkAdmin();
        (new DepartmentController())->create();
        break;

    case preg_match('#^/department/(\d+)$#', $uri, $matches) && $method === 'PUT':
        $user = AuthMiddleware::checkAdmin();
        $id = $matches[1];
        (new DepartmentController())->update($id);
        break;
    case preg_match('#^/department/(\d+)$#', $uri, $matches) && $method === 'DELETE':
        $user = AuthMiddleware::checkAdmin();
        $id = $matches[1];
        (new DepartmentController())->delete($id);
        break;



    default:
        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
}
