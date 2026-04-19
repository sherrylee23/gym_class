<?php
// author: Cheok Jia Xuen

require_once('UserModel.php');

class UserFacade {
    private $model;

    public function __construct() {
        $this->model = new UserModel();
    }

    public function registerUser($fullName, $email, $phone, $password) {
        return $this->model->register($fullName, $email, $phone, $password);
    }

    public function getUserByEmail($email) {
        return $this->model->findUserByEmail($email);
    }

    public function getUserById($id) {
        return $this->model->findUserById($id);
    }

    public function updateUserProfile($id, $name, $phone, $password = null) {
        return $this->model->updateProfile($id, $name, $phone, $password);
    }

    public function updateUserRole($userId, $newRole) {
        return $this->model->updateRole($userId, $newRole);
    }

    public function increaseFailedAttempts($id) {
        return $this->model->increaseAttempts($id);
    }

    public function resetFailedAttempts($id) {
        return $this->model->resetAttempts($id);
    }
}