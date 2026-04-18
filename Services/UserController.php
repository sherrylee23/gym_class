<?php

require_once('../Model/UserFacade.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class UserController {

    private $facade;

    public function __construct() {
        $this->facade = new UserFacade();
    }

    private function validateCsrfToken($data) {
        if (!isset($_SESSION['csrf_token']) || !isset($data['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $data['csrf_token']);
    }

    public function registerUser($data) {
        if (!$this->validateCsrfToken($data)) {
            return "Invalid request token.";
        }

        $phone = isset($data['phone_number']) ? trim($data['phone_number']) : ($data['phone'] ?? null);
        $fullName = trim($data['full_name'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if (empty($fullName) || empty($email) || empty($phone) || empty($password)) {
            return "Please fill in all required fields, including your phone number.";
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Invalid email format.";
        }

        if (!preg_match('/^[0-9]{11}$/', $phone)) {
            return "Invalid Phone Format! Please enter exactly 11 numbers (e.g., 01234567890).";
        }

        $result = $this->facade->registerUser($fullName, $email, $phone, $password);

        if ($result) {
            header("Location: login.php?registration=success");
            exit;
        } else {
            return "This email is already registered. Please use a different email or login.";
        }
    }

    public function loginUser($email, $password, $csrfToken = null) {
        if (!isset($_SESSION['csrf_token']) || !$csrfToken || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
            return "Invalid request token.";
        }

        $email = trim($email);
        $user = $this->facade->getUserByEmail($email);

        if (!$user) {
            return "Invalid email or password.";
        }

        if (!empty($user['lock_until']) && strtotime($user['lock_until']) > time()) {
            return "Account temporarily locked. Please try again later.";
        }

        if (password_verify($password, $user['password'])) {
            $this->facade->resetFailedAttempts($user['id']);

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            header("Location: profile.php");
            exit;
        } else {
            $this->facade->increaseFailedAttempts($user['id']);
            return "Invalid email or password.";
        }
    }

    public function updateProfile($data) {
        if (!isset($_SESSION['user_id'])) {
            return "Session expired. Please login.";
        }

        if (!$this->validateCsrfToken($data)) {
            return "Invalid request token.";
        }

        $name = trim($data['full_name'] ?? '');
        $phone = trim($data['phone_number'] ?? '');
        $password = !empty($data['password']) ? $data['password'] : null;

        if (empty($name) || empty($phone)) {
            return "Please fill in all required fields.";
        }

        if (!preg_match('/^[0-9]{11}$/', $phone)) {
            return "Error: Phone number must be exactly 11 digits (e.g., 01234567890).";
        }

        $result = $this->facade->updateUserProfile($_SESSION['user_id'], $name, $phone, $password);

        if ($result) {
            $_SESSION['user_name'] = $name;
            header("Location: profile.php?update=success");
            exit;
        } else {
            return "Database error: Could not update profile.";
        }
    }

    public function changeUserRole($data) {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
            return "Unauthorized: Only Admins can change roles.";
        }

        if (!$this->validateCsrfToken($data)) {
            return "Invalid request token.";
        }

        $userId = $data['user_id'] ?? null;
        $newRole = $data['role'] ?? '';

        $allowedRoles = ['User', 'Member', 'Trainer', 'Admin'];
        if (!in_array($newRole, $allowedRoles)) {
            return "Invalid role selected.";
        }

        $result = $this->facade->updateUserRole($userId, $newRole);

        if ($result) {
            if ($newRole === 'Trainer') {
                $trainerPath = dirname(__DIR__) . '/Model/Trainer.php';
                if (file_exists($trainerPath)) {
                    require_once($trainerPath);

                    $user = $this->facade->getUserById($userId);
                    if ($user && class_exists('Trainer')) {
                        Trainer::create($userId, $user['full_name']);
                    }
                }
            }

            header("Location: DisplayUsers.php?status=updated");
            exit;
        } else {
            return "System Error: Could not update the database.";
        }
    }
}