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

    /**
     * 修改点：更加严谨的冲突检查逻辑
     * 解决同一时间段、同一课程名重复的问题
     */
    public static function isConflict($class_name, $date, $start, $end) {
        $db = getDBConnection();
        
        // 使用更稳健的时间重叠算法：
        // 只要 (已有课程开始 < 新课程结束) 并且 (已有课程结束 > 新课程开始)
        // 就能捕捉到所有的重叠情况（完全包含、部分重叠、完全一致）
        $sql = "SELECT COUNT(*) FROM schedules 
                WHERE class_name = ? 
                AND class_date = ? 
                AND (start_time < ? AND end_time > ?)";
                
        $stmt = $db->prepare($sql);
        
        // 参数对应关系：
        // ?1 -> 课程名
        // ?2 -> 日期
        // ?3 -> 想要预订的 结束时间 ($end)
        // ?4 -> 想要预订的 开始时间 ($start)
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