<?php

require_once('UserModel.php');
if (session_status() === PHP_SESSION_NONE)
    session_start();

class UserController {

    private $model;

    public function __construct() {
        $this->model = new UserModel();
    }

    // 2.1.1 User Registration Logic (The missing method)
    public function registerUser($data) {
        // Check if the form used 'phone' or 'phone_number'
        $phone = isset($data['phone_number']) ? $data['phone_number'] : ($data['phone'] ?? null);

        if (!preg_match('/^[0-9]{11}$/', $data['phone_number'])) {
            return "Invalid Phone Format! Please enter exactly 11 numbers (e.g., 01234567890).";
        }

        if (empty($data['full_name']) || empty($data['email']) || empty($phone) || empty($data['password'])) {
            return "Please fill in all required fields, including your phone number.";
        }

        $result = $this->model->register(
                $data['full_name'],
                $data['email'],
                $phone,
                $data['password']
        );

        if ($result) {
            header("Location: login.php?registration=success");
            exit;
        } else {
            return "This email is already registered. Please use a different email or login.";
        }
    }

    // Rename or add this to match your login.php call
    public function loginUser($email, $password) {
        $user = $this->model->findUserByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role']; // 2.1.4 RBAC logic 
            header("Location: profile.php");
            exit;
        } else {
            return "Invalid email or password.";
        }
    }

    // Add this inside your UserController class
    public function updateProfile($data) {
        if (!isset($_SESSION['user_id']))
            return "Session expired. Please login.";

        // 1. Get the data and remove any accidental spaces
        $name = trim($data['full_name']);
        $phone = trim($data['phone_number']);
        $password = !empty($data['password']) ? $data['password'] : null;

        // 2. Validation: Check if it is EXACTLY 11 digits
        // ^[0-9]{11}$ means: Start, exactly 11 numbers, End.
        if (!preg_match('/^[0-9]{11}$/', $phone)) {
            return "Error: Phone number must be exactly 11 digits (e.g., 01234567890).";
        }

        // 3. If validation passes, proceed to Model
        $result = $this->model->updateProfile($_SESSION['user_id'], $name, $phone, $password);

        if ($result) {
            $_SESSION['user_name'] = $name;
            header("Location: profile.php?update=success");
            exit;
        } else {
            return "Database error: Could not update profile.";
        }
    }

    public function changeUserRole($data) {
        // 1. Security Check: Only Admins allowed
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
            return "Unauthorized: Only Admins can change roles.";
        }

        $userId = $data['user_id'];
        $newRole = $data['role'];

        // 2. Data Validation
        $allowedRoles = ['Member', 'Trainer', 'Admin'];
        if (!in_array($newRole, $allowedRoles)) {
            return "Invalid role selected.";
        }

        // 3. Call the Model to perform the update
        $result = $this->model->updateRole($userId, $newRole);

        if ($result) {
            // check is change to trainer
            if ($newRole === 'Trainer') {
                require_once('../Model/Trainer.php');

                $user = $this->model->findUserById($userId);
                if ($user) {
                    Trainer::create($userId, $user['full_name']);
                }
            }
            // Redirect back to the user list with a success message
            header("Location: DisplayUsers.php?status=updated");
            exit;
        } else {
            return "System Error: Could not update the database.";
        }
    }
}
