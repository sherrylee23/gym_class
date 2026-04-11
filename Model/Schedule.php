<?php

require_once('../user_management/Database.php');

class Schedule {

    public $id;
    public $trainer_id;
    public $class_name;
    public $class_date;
    public $start_time;
    public $end_time;
    public $max_capacity;

    public static function getAll() {
        $db = getDBConnection();
        $stmt = $db->query("SELECT s.*, t.full_name as trainer_name 
                            FROM schedules s 
                            JOIN trainers t ON s.trainer_id = t.id 
                            ORDER BY class_date, start_time");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public static function isConflict($class_name, $date, $start, $end) {
        $db = getDBConnection();
        // make sure not in the same class and same time
        $sql = "SELECT COUNT(*) FROM schedules 
                WHERE class_name = ? 
                AND class_date = ? 
                AND (start_time < ? AND end_time > ?)";
                
        $stmt = $db->prepare($sql);
        

        $stmt->execute([$class_name, $date, $end, $start]);
        
        return $stmt->fetchColumn() > 0;
    }

    public function save() {
        $db = getDBConnection();
        $sql = "INSERT INTO schedules (trainer_id, class_name, class_date, start_time, end_time, max_capacity) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $this->trainer_id,
            $this->class_name,
            $this->class_date,
            $this->start_time,
            $this->end_time,
            $this->max_capacity
        ]);
    }

    public static function delete($id) {
        $db = getDBConnection();
        $stmt = $db->prepare("DELETE FROM schedules WHERE id = ?");
        return $stmt->execute([$id]);
    }
}