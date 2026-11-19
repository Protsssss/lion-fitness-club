<?php
require_once __DIR__ . '/../models/Contact.php';

class ContactController {

    public function send() {
        $data      = json_decode(file_get_contents("php://input"), true);
        $name      = trim($data['name']      ?? '');
        $telephone = trim($data['telephone'] ?? '');
        $email     = trim($data['email']     ?? '');
        $subject   = trim($data['subject']   ?? '');
        $message   = trim($data['message']   ?? '');

        if (!$name || !$telephone || !$email || !$message) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Заповніть всі обов'язкові поля"]);
            return;
        }

        $model = new Contact();
        if ($model->create($name, $telephone, $email, $subject, $message)) {
            echo json_encode(["success" => true, "message" => "Ваш запит успішно відправлено!"]);
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Помилка сервера"]);
        }
    }

    // GET /api/contact/all — admin і trainer_edit
    public function getAll() {
        $role = $_SESSION['role'] ?? '';
        if (!in_array($role, ['admin', 'trainer_edit'])) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Доступ заборонено"]);
            return;
        }
        $model = new Contact();
        echo json_encode(["success" => true, "requests" => $model->getAll()]);
    }
}
