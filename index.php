<?php
session_start();

$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = rtrim($uri, '/');
$method = $_SERVER['REQUEST_METHOD'];

$uri = str_replace('/cyber-platform', '', $uri);

if (str_starts_with($uri, '/api')) {
    header("Content-Type: application/json; charset=utf-8");

    if (preg_match('#^/api/(login|register|logout|user|users|trainer)#', $uri)) {
        require_once __DIR__ . '/controllers/UserController.php';
        $c = new UserController();
        if      ($uri === '/api/login'                       && $method === 'POST') $c->login();
        elseif  ($uri === '/api/register'                    && $method === 'POST') $c->register();
        elseif  ($uri === '/api/logout'                      && $method === 'POST') $c->logout();
        elseif  ($uri === '/api/user'                        && $method === 'GET')  $c->getProfile();
        elseif  ($uri === '/api/user/update'                 && $method === 'POST') $c->updateProfile();
        elseif  ($uri === '/api/users'                       && $method === 'GET')  $c->getAllUsers();
        elseif  ($uri === '/api/users/update'                && $method === 'POST') $c->updateUser();
        elseif  ($uri === '/api/users/delete'                && $method === 'POST') $c->deleteUser();
        elseif  ($uri === '/api/users/trainers'              && $method === 'GET')  $c->getTrainers();
        elseif  ($uri === '/api/users/clients-with-trainer'  && $method === 'GET')  $c->getClientsWithTrainer();
        elseif  ($uri === '/api/users/assign-trainer'        && $method === 'POST') $c->assignTrainer();
        elseif  ($uri === '/api/trainer/clients'             && $method === 'GET')  $c->getTrainerClients();
        else { http_response_code(404); echo json_encode(["error" => "Не знайдено"]); }

    } elseif (str_starts_with($uri, '/api/contact')) {
        require_once __DIR__ . '/controllers/ContactController.php';
        $c = new ContactController();
        if      ($uri === '/api/contact'     && $method === 'POST') $c->send();
        elseif  ($uri === '/api/contact/all' && $method === 'GET')  $c->getAll();
        else { http_response_code(404); echo json_encode(["error" => "Не знайдено"]); }

    } elseif (str_starts_with($uri, '/api/shop')) {
        require_once __DIR__ . '/controllers/ShopController.php';
        $c = new ShopController();
        if      ($uri === '/api/shop/purchase'  && $method === 'POST') $c->purchase();
        elseif  ($uri === '/api/shop/purchases' && $method === 'GET')  $c->getAll();
        else { http_response_code(404); echo json_encode(["error" => "Не знайдено"]); }

    } elseif (str_starts_with($uri, '/api/subscription')) {
        require_once __DIR__ . '/controllers/SubscriptionController.php';
        $c = new SubscriptionController();
        if      ($uri === '/api/subscription'     && $method === 'POST') $c->purchase();
        elseif  ($uri === '/api/subscription/all' && $method === 'GET')  $c->getAll();
        else { http_response_code(404); echo json_encode(["error" => "Не знайдено"]); }

    } else {
        http_response_code(404);
        echo json_encode(["error" => "Маршрут не знайдено"]);
    }

} else {
    header("Content-Type: text/html; charset=utf-8");

    if ($uri === '/admin') {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header("Location: /cyber-platform/login"); exit;
        }
        require_once __DIR__ . '/views/admin.html';

    } elseif ($uri === '/trainer-edit') {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'trainer_edit') {
            header("Location: /cyber-platform/login"); exit;
        }
        require_once __DIR__ . '/views/trainer_edit.html';

    } elseif ($uri === '/trainer') {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'trainer') {
            header("Location: /cyber-platform/login"); exit;
        }
        require_once __DIR__ . '/views/trainer.html';

    } elseif ($uri === '/dashboard') {
        if (!isset($_SESSION['username'])) {
            header("Location: /cyber-platform/login"); exit;
        }
        require_once __DIR__ . '/views/dashboard.html';

    } elseif ($uri === '' || $uri === '/home') require_once __DIR__ . '/home.php';
    elseif  ($uri === '/login')               require_once __DIR__ . '/views/login.html';
    elseif  ($uri === '/register')            require_once __DIR__ . '/views/registration.html';
    else { http_response_code(404); echo "<h1>404 — Сторінку не знайдено</h1>"; }
}
