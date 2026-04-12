<?php

require_once('../user_management/Database.php');

class Trainer {
    public $id;
    public $full_name;
    public $specialty;

    // get all trainer
    public static function getAll() {
        $db = getDBConnection();
        $stmt = $db->query("SELECT id, full_name, specialty FROM trainers");
        return $stmt->fetchAll(PDO::FETCH_CLASS, 'Trainer');
    }

    // find trainer
    public static function findById($id) {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT id, full_name, specialty FROM trainers WHERE id = ?");
        $stmt->execute([$id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'Trainer');
        return $stmt->fetch();
    }
    
    public static function create($userId, $fullName) {
        $db = getDBConnection();
        // ignore when duplicate id
        $stmt = $db->prepare("INSERT IGNORE INTO trainers (id, full_name, specialty) VALUES (?, ?, 'General Fitness')");
        return $stmt->execute([$userId, $fullName]);
    }

}
?>

