<?php
require_once __DIR__ . '/../config/database.php';

class Subscription {
    private $conn;

    public function __construct() {
        $this->conn = getConnection();
    }

    // Зберегти запит на абонемент в таблицю purchases
    public function create($data) {
        $stmt = $this->conn->prepare(
            "INSERT INTO purchases
             (name, email, phone, card, additional_info, abonement_type)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssssss",
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['card'],
            $data['additional_info'],
            $data['abonement_type']
        );
        return $stmt->execute();
    }

    // Отримати всі запити (для адміна)
    public function getAll() {
        $result = $this->conn->query(
            "SELECT * FROM purchases ORDER BY id DESC"
        );
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
