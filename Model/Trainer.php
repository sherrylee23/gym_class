<?php

require_once('../user_management/Database.php');

class Trainer {
    public $id;
    public $full_name;
    public $specialty;

    // get trainer
    public static function getAll() {
        $db = getDBConnection();
        $stmt = $db->query("SELECT id, full_name, specialty FROM trainers");
        // 返回对象数组，而不是纯数组，这更符合 ORM 的定义
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
        // 使用 INSERT IGNORE 防止重复插入同一个 ID
        $stmt = $db->prepare("INSERT IGNORE INTO trainers (id, full_name, specialty) VALUES (?, ?, 'General Fitness')");
        return $stmt->execute([$userId, $fullName]);
    }

}
?>

