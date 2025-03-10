<?php
require "controllers/UserController.php";

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($uri, '/'));

if (count($segments) === 4 && $segments[0] === 'BE_Test' && $segments[1] === 'api' && $segments[2] === 'users' && is_numeric($segments[3])) {
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        UserController::deleteUser($segments[3]);
    } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        UserController::getUserById($segments[3]);
    }
    exit;
}

switch ($uri) {
    case '/BE_Test/api/login':
        UserController::login();
        break;
    case '/BE_Test/api/users':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            UserController::getAllUsers();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            UserController::createUser();
        }
        break;
    default:
        http_response_code(404);
        echo json_encode(["message" => "Route not found"]);
}
