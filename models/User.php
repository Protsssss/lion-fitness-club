<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;

    public function __construct() {
        $this->conn = getConnection();
    }

public function findByUsername($username) {
    $stmt = $this->conn->prepare(
        "SELECT c.*, t.full_name as assigned_trainer_name
         FROM clients c
         LEFT JOIN client_trainer_relationships r ON c.id = r.client_id
         LEFT JOIN clients t ON r.trainer_id = t.id
         WHERE c.username = ?"
    );
    $stmt->bind_param("s", $username);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

    public function create($username, $password, $email) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare(
            "INSERT INTO clients (username, password, email) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("sss", $username, $hash, $email);
        return $stmt->execute();
    }

    public function updateProfile($username, $full_name, $height, $weight) {
        $stmt = $this->conn->prepare(
            "UPDATE clients SET full_name=?, height=?, weight=? WHERE username=?"
        );
        $stmt->bind_param("ssss", $full_name, $height, $weight, $username);
        return $stmt->execute();
    }

    // Оновити юзера адміном (роль, абонемент, ім'я, email)
    public function updateById($id, $full_name, $email, $role, $subscription) {
        $stmt = $this->conn->prepare(
            "UPDATE clients SET full_name=?, email=?, role=?, subscription=? WHERE id=?"
        );
        $stmt->bind_param("ssssi", $full_name, $email, $role, $subscription, $id);
        return $stmt->execute();
    }

    public function getAllUsers() {
        $result = $this->conn->query(
            "SELECT id, username, email, full_name, role, subscription, trainer_type FROM clients ORDER BY id"
        );
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function deleteById($id) {
        $stmt = $this->conn->prepare("DELETE FROM clients WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function getClientsByTrainer($trainer_id) {
        $stmt = $this->conn->prepare(
            "SELECT c.full_name, c.email, c.height, c.weight, c.subscription
             FROM clients c
             INNER JOIN client_trainer_relationships r ON c.id = r.client_id
             WHERE r.trainer_id = ?"
        );
        $stmt->bind_param("i", $trainer_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }


    // Отримати всіх тренерів (для списку в адмінці)
    public function getAllTrainers() {
        $result = $this->conn->query(
            "SELECT id, username, full_name FROM clients WHERE role IN ('trainer','trainer_edit') ORDER BY full_name"
        );
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Отримати поточного тренера клієнта
    public function getClientTrainer($client_id) {
        $stmt = $this->conn->prepare(
            "SELECT c.id, c.username, c.full_name FROM clients c
             INNER JOIN client_trainer_relationships r ON c.id = r.trainer_id
             WHERE r.client_id = ?"
        );
        $stmt->bind_param("i", $client_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Призначити тренера клієнту (видалити старий зв'язок і додати новий)
    public function assignTrainer($client_id, $trainer_id) {
        // Видаляємо старий зв'язок якщо є
        $stmt = $this->conn->prepare("DELETE FROM client_trainer_relationships WHERE client_id = ?");
        $stmt->bind_param("i", $client_id);
        $stmt->execute();

        // Якщо trainer_id = 0, просто видаляємо зв'язок (відкріпити)
        if (!$trainer_id) return true;

        // Додаємо новий зв'язок
        $stmt = $this->conn->prepare("INSERT INTO client_trainer_relationships (client_id, trainer_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $client_id, $trainer_id);
        return $stmt->execute();
    }

    // Отримати всіх клієнтів з інфо про їх тренера (для адмінки)
    public function getAllClientsWithTrainer() {
        $result = $this->conn->query(
            "SELECT c.id, c.username, c.full_name, c.email, c.subscription,
                    t.id as trainer_id, t.full_name as trainer_name
             FROM clients c
             LEFT JOIN client_trainer_relationships r ON c.id = r.client_id
             LEFT JOIN clients t ON r.trainer_id = t.id
             WHERE c.role = 'client'
             ORDER BY c.id"
        );
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
