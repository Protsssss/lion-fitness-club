<?php
require_once __DIR__ . '/../config/database.php';

class Shop {
    private $conn;

    public function __construct() {
        $this->conn = getConnection();
    }

    // Зберегти покупку в shop_purchases
    public function createPurchase($data) {
        $stmt = $this->conn->prepare(
            "INSERT INTO shop_purchases
             (product_name, name, email, phone, delivery, quantity, address, card, additional_info, total_price)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "sssssiissd",
            $data['product_name'],
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['delivery'],
            $data['quantity'],
            $data['address'],
            $data['card'],
            $data['additional_info'],
            $data['total_price']
        );
        return $stmt->execute();
    }

    // Отримати всі покупки (для адміна)
    public function getAll() {
        $result = $this->conn->query(
            "SELECT * FROM shop_purchases ORDER BY id DESC"
        );
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
