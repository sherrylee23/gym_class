<?php

require_once('Database.php');

class UserModel {

    private $db;

    public function __construct() {
        $this->db = getDBConnection();
    }

    // 2.1.1 User Registration
    public function register($name, $email, $phone, $password) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $query = "INSERT INTO users (full_name, email, phone_number, password, failed_attempts, lock_until) 
                      VALUES (:name, :email, :phone, :password, 0, NULL)";
            $stmt = $this->db->prepare($query);

            return $stmt->execute([
                        ':name' => $name,
                        ':email' => $email,
                        ':phone' => $phone,
                        ':password' => $hashedPassword
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return false;
            }
            throw $e;
        }
    }

    // 2.1.2 & 2.1.4 Login and Role Fetching
    public function findUserByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findUserById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
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

    public function updateRole($userId, $newRole) {
        try {
            $stmt = $this->db->prepare("UPDATE users SET role = ? WHERE id = ?");
            return $stmt->execute([$newRole, $userId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Brute force protection
    public function increaseAttempts($id) {
        $user = $this->findUserById($id);
        if (!$user) {
            return false;
        }

        $currentAttempts = (int) ($user['failed_attempts'] ?? 0);
        $newAttempts = $currentAttempts + 1;

        if ($newAttempts >= 5) {
            $lockUntil = date('Y-m-d H:i:s', strtotime('+1 minutes'));
            $stmt = $this->db->prepare("UPDATE users SET failed_attempts = ?, lock_until = ? WHERE id = ?");
            return $stmt->execute([$newAttempts, $lockUntil, $id]);
        } else {
            $stmt = $this->db->prepare("UPDATE users SET failed_attempts = ? WHERE id = ?");
            return $stmt->execute([$newAttempts, $id]);
        }
    }

    public function resetAttempts($id) {
        $stmt = $this->db->prepare("UPDATE users SET failed_attempts = 0, lock_until = NULL WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

?>