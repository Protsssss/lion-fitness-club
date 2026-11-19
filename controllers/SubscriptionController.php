<?php
require_once __DIR__ . '/../models/Subscription.php';

class SubscriptionController {

    public function purchase() {
        $data = json_decode(file_get_contents("php://input"), true);

        $abonement_type  = trim($data['abonement_type']  ?? '');
        $name            = trim($data['name']            ?? '');
        $email           = trim($data['email']           ?? '');
        $phone           = trim($data['phone']           ?? '');
        $card            = trim($data['card']            ?? '');
        $additional_info = trim($data['additional_info'] ?? '');

        if (!$abonement_type || !$name || !$phone) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Заповніть всі обов'язкові поля"]);
            return;
        }

        $model = new Subscription();
        if ($model->create(compact('abonement_type','name','email','phone','card','additional_info'))) {
            echo json_encode(["success" => true, "message" => "Запит на абонемент прийнято!"]);
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Помилка сервера"]);
        }
    }

    // GET /api/subscription/all — admin і trainer_edit
    public function getAll() {
        $role = $_SESSION['role'] ?? '';
        if (!in_array($role, ['admin', 'trainer_edit'])) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Доступ заборонено"]);
            return;
        }
        $model = new Subscription();
        echo json_encode(["success" => true, "subscriptions" => $model->getAll()]);
    }
}
