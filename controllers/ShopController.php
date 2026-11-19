<?php
require_once __DIR__ . '/../models/Shop.php';

class ShopController {

    public function purchase() {
        $data = json_decode(file_get_contents("php://input"), true);

        $required = ['product_name', 'name', 'email', 'phone', 'delivery', 'quantity', 'card'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "Заповніть поле: $field"]);
                return;
            }
        }

        $purchase = [
            'product_name'    => $data['product_name'],
            'name'            => $data['name'],
            'email'           => $data['email'],
            'phone'           => $data['phone'],
            'delivery'        => $data['delivery'],
            'quantity'        => intval($data['quantity']),
            'address'         => $data['address'] ?? '',
            'card'            => $data['card'],
            'additional_info' => $data['additional_info'] ?? '',
            'total_price'     => floatval($data['total_price'] ?? 0),
        ];

        $model = new Shop();
        if ($model->createPurchase($purchase)) {
            echo json_encode(["success" => true, "message" => "Замовлення прийнято!"]);
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Помилка сервера"]);
        }
    }

    // GET /api/shop/purchases — admin і trainer_edit
    public function getAll() {
        $role = $_SESSION['role'] ?? '';
        if (!in_array($role, ['admin', 'trainer_edit'])) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Доступ заборонено"]);
            return;
        }
        $model = new Shop();
        echo json_encode(["success" => true, "purchases" => $model->getAll()]);
    }
}
