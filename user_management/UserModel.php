<?php

require_once('Database.php');

class UserModel {

    private $db;

    public function __construct() {
        $this->db = getDBConnection(); // Uses Singleton [cite: 47, 85]
    }

    // 2.1.1 User Registration
    public function register($name, $email, $phone, $password) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $query = "INSERT INTO users (full_name, email, phone_number, password) VALUES (:name, :email, :phone, :password)";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                        ':name' => $name,
                        ':email' => $email,
                        ':phone' => $phone,
                        ':password' => $hashedPassword
            ]);
        } catch (PDOException $e) {
            // Check if the error is a "Duplicate Entry" (SQLSTATE 23000)
            if ($e->getCode() == 23000) {
                return false;
            }
            throw $e; // Rethrow other unexpected errors
        }
    }

    // 2.1.2 & 2.1.4 Login and Role Fetching
    public function findUserByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]); // [cite: 51, 89]
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 2.1.3 Manage Profile
    public function updateProfile($id, $name, $phone, $password = null) {
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $this->db->prepare("UPDATE users SET full_name = ?, phone_number = ?, password = ? WHERE id = ?");
            return $stmt->execute([$name, $phone, $hashed, $id]);
        } else {
            $stmt = $this->db->prepare("UPDATE users SET full_name = ?, phone_number = ? WHERE id = ?");
            return $stmt->execute([$name, $phone, $id]);
        }
    }

    // Inside class UserModel
    public function updateRole($userId, $newRole) {
        try {
            $stmt = $this->db->prepare("UPDATE users SET role = ? WHERE id = ?");
            return $stmt->execute([$newRole, $userId]);
        } catch (PDOException $e) {
            return false;
        }
    }
}

?>