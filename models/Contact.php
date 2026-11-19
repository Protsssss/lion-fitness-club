<?php
require_once __DIR__ . '/../config/database.php';

class Contact {
    private $conn;

    public function __construct() {
        $this->conn = getConnection();
    }

    // Зберегти контактний запит
    public function create($name, $telephone, $email, $subject, $message) {
        $stmt = $this->conn->prepare(
            "INSERT INTO contact_requests (name, telephone, email, subject, message)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssss", $name, $telephone, $email, $subject, $message);
        return $stmt->execute();
    }

    // Отримати всі запити (для адміна)
    public function getAll() {
        $result = $this->conn->query(
            "SELECT * FROM contact_requests ORDER BY id DESC"
        );
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
