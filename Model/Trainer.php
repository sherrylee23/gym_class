<?php
require_once('Database.php');

class Trainer {
    public $trainer_id;
    public $full_name;
    public $specialty;

    
    // security: mitigation of sensitive data exposure --> filter only public data is sent to API (encapsulation)
    private static function toSafeArray($trainer) {
        return [
            'trainer_id' => $trainer->trainer_id,
            'full_name'  => htmlspecialchars($trainer->full_name),
            'specialty'  => htmlspecialchars($trainer->specialty)
        ];
    }

    public static function getAll() {
        $db = getDBConnection();
        $stmt = $db->query("SELECT trainer_id, full_name, specialty FROM trainers");
        $trainers = $stmt->fetchAll(PDO::FETCH_CLASS, 'Trainer');
        // Apply the safety filter to all results
        return array_map([self::class, 'toSafeArray'], $trainers);
    }

    public static function findById($id) {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT trainer_id, full_name, specialty FROM trainers WHERE trainer_id = ?");
        $stmt->execute([$id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'Trainer');
        $trainer = $stmt->fetch();
        return $trainer ? self::toSafeArray($trainer) : null;
    }

    public static function create($userId, $fullName) {
        $db = getDBConnection();
        $stmt = $db->prepare("INSERT IGNORE INTO trainers (trainer_id, full_name, specialty) VALUES (?, ?, 'General Fitness')");
        return $stmt->execute([$userId, $fullName]);
    }
}