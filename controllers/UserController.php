<?php
require_once __DIR__ . '/../models/User.php';

class UserController {

    public function login() {
        $data     = json_decode(file_get_contents("php://input"), true);
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        $userModel = new User();
        $user      = $userModel->findByUsername($username);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['username'] = $username;
            $_SESSION['role']     = $user['role'];
            $_SESSION['user_id']  = $user['id'];

            echo json_encode([
                "success" => true,
                "role"    => $user['role'],
                "message" => "Успішний вхід"
            ]);
        } else {
            http_response_code(401);
            echo json_encode(["success" => false, "message" => "Невірний логін або пароль"]);
        }
    }

    public function register() {
        $data     = json_decode(file_get_contents("php://input"), true);
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';
        $email    = trim($data['email'] ?? '');

        if (!$username || !$password || !$email) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Заповніть всі поля"]);
            return;
        }

        $userModel = new User();
        if ($userModel->findByUsername($username)) {
            http_response_code(409);
            echo json_encode(["success" => false, "message" => "Такий логін вже зайнятий"]);
            return;
        }

        if ($userModel->create($username, $password, $email)) {
            echo json_encode(["success" => true, "message" => "Реєстрація успішна"]);
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Помилка сервера"]);
        }
    }

    public function logout() {
        session_destroy();
        echo json_encode(["success" => true]);
    }

    public function getProfile() {
        if (!isset($_SESSION['username'])) {
            http_response_code(401);
            echo json_encode(["success" => false, "message" => "Не авторизовано"]);
            return;
        }
        $userModel = new User();
        $user      = $userModel->findByUsername($_SESSION['username']);
        unset($user['password']);
        echo json_encode(["success" => true, "user" => $user]);
    }

    public function updateProfile() {
        if (!isset($_SESSION['username'])) {
            http_response_code(401);
            echo json_encode(["success" => false, "message" => "Не авторизовано"]);
            return;
        }
        $data      = json_decode(file_get_contents("php://input"), true);
        $full_name = trim($data['full_name'] ?? '');
        $height    = $data['height'] ?? '';
        $weight    = $data['weight'] ?? '';

        $userModel = new User();
        if ($userModel->updateProfile($_SESSION['username'], $full_name, $height, $weight)) {
            echo json_encode(["success" => true, "message" => "Дані оновлено"]);
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Помилка оновлення"]);
        }
    }

    public function getAllUsers() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Доступ заборонено"]);
            return;
        }
        $userModel = new User();
        echo json_encode(["success" => true, "users" => $userModel->getAllUsers()]);
    }

    // POST /api/users/update  — редагування юзера адміном
    public function updateUser() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Доступ заборонено"]);
            return;
        }
        $data         = json_decode(file_get_contents("php://input"), true);
        $id           = intval($data['id'] ?? 0);
        $full_name    = trim($data['full_name']   ?? '');
        $email        = trim($data['email']        ?? '');
        $role         = trim($data['role']         ?? '');
        $subscription = trim($data['subscription'] ?? '');

        $allowed_roles = ['client', 'trainer', 'trainer_edit', 'admin'];
        if (!$id || !in_array($role, $allowed_roles)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Невірні дані"]);
            return;
        }

        $userModel = new User();
        if ($userModel->updateById($id, $full_name, $email, $role, $subscription)) {
            echo json_encode(["success" => true, "message" => "Користувача оновлено"]);
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Помилка оновлення"]);
        }
    }

    public function deleteUser() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Доступ заборонено"]);
            return;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $id   = intval($data['id'] ?? 0);

        if (!$id) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Невірний ID"]);
            return;
        }
        $userModel = new User();
        if ($userModel->deleteById($id)) {
            echo json_encode(["success" => true]);
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Помилка видалення"]);
        }
    }

    public function getTrainerClients() {
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['trainer', 'trainer_edit'])) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Доступ заборонено"]);
            return;
        }
        $userModel = new User();
        $trainer   = $userModel->findByUsername($_SESSION['username']);
        $clients   = $userModel->getClientsByTrainer($trainer['id']);
        unset($trainer['password']);
        echo json_encode(["success" => true, "trainer" => $trainer, "clients" => $clients]);
    }

    // GET /api/users/clients-with-trainer — клієнти з тренерами (для адміна)
    public function getClientsWithTrainer() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Доступ заборонено"]);
            return;
        }
        $userModel = new User();
        echo json_encode(["success" => true, "clients" => $userModel->getAllClientsWithTrainer()]);
    }

    // GET /api/users/trainers — список тренерів (для адміна)
    public function getTrainers() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Доступ заборонено"]);
            return;
        }
        $userModel = new User();
        echo json_encode(["success" => true, "trainers" => $userModel->getAllTrainers()]);
    }

    // POST /api/users/assign-trainer — призначити тренера клієнту
    public function assignTrainer() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Доступ заборонено"]);
            return;
        }
        $data       = json_decode(file_get_contents("php://input"), true);
        $client_id  = intval($data['client_id']  ?? 0);
        $trainer_id = intval($data['trainer_id']  ?? 0);

        if (!$client_id) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Невірний client_id"]);
            return;
        }

        $userModel = new User();
        if ($userModel->assignTrainer($client_id, $trainer_id)) {
            $msg = $trainer_id ? "Тренера призначено" : "Тренера відкріплено";
            echo json_encode(["success" => true, "message" => $msg]);
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Помилка збереження"]);
        }
    }
}
