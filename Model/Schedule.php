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
    public $is_free;

    /**
     * Requirement: Fetch all available schedules.
     * Logic: 
     * 1. Hides classes that have reached max capacity.
     * 2. Hides classes where the date is in the past.
     * 3. Hides classes happening today if the start_time has already passed.
     */
    public static function getAll($currentUserId = 0) {
        $db = getDBConnection();
        
        // Get current date and time for strict filtering
        $currentDate = date('Y-m-d');
        $currentTime = date('H:i:s');

        $sql = "SELECT s.*, t.full_name as trainer_name, 
                (s.max_capacity - (SELECT COUNT(*) FROM bookings b2 WHERE b2.schedule_id = s.id AND b2.status = 'Confirmed')) as available_slots,
                
                /* 1. Check if user already booked THIS specific class */
                (SELECT COUNT(*) FROM bookings b3 WHERE b3.schedule_id = s.id AND b3.user_id = ? AND b3.status = 'Confirmed') as user_booked,
                
                /* 2. Check for TIME OVERLAPS with user's other bookings on the same day */
                (SELECT COUNT(*) FROM bookings b4 
                 JOIN schedules s2 ON b4.schedule_id = s2.id 
                 WHERE b4.user_id = ? 
                 AND b4.status = 'Confirmed'
                 AND s2.class_date = s.class_date
                 AND (s.start_time < s2.end_time AND s.end_time > s2.start_time)
                ) as time_conflict

                FROM schedules s 
                /* FIXED: Changed t.id to t.trainer_id */
                LEFT JOIN trainers t ON s.trainer_id = t.trainer_id
                
                WHERE 
                    /* FILTER A: Capacity Check - Only show if slots are greater than 0 */
                    (s.max_capacity > (SELECT COUNT(*) FROM bookings b5 WHERE b5.schedule_id = s.id AND b5.status = 'Confirmed'))
                    
                    AND (
                        /* FILTER B: Future Date Check */
                        s.class_date > ? 
                        OR 
                        /* FILTER C: Today's Start Time Check (disappear if started/ended) */
                        (s.class_date = ? AND s.start_time > ?)
                    )

                GROUP BY s.id
                ORDER BY s.class_date, s.start_time";
        
        $stmt = $db->prepare($sql);
        
        /* execute mapping:
           1. ? -> $currentUserId (user_booked check)
           2. ? -> $currentUserId (time_conflict check)
           3. ? -> $currentDate (Future date)
           4. ? -> $currentDate (Today match)
           5. ? -> $currentTime (Start time hasn't passed)
        */
        $stmt->execute([$currentUserId, $currentUserId, $currentDate, $currentDate, $currentTime]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Checks if a class name already exists at a certain time (Global Conflict)
     */
    public static function isConflict($class_name, $date, $start, $end) {
        $db = getDBConnection();
        $sql = "SELECT COUNT(*) FROM schedules 
                WHERE class_name = ? 
                AND class_date = ? 
                AND (start_time < ? AND end_time > ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$class_name, $date, $end, $start]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Saves a new schedule created by a Trainer
     */
    public function save() {
        $db = getDBConnection();
        // UPDATED: Added is_free to the INSERT statement
        $sql = "INSERT INTO schedules (trainer_id, class_name, class_date, start_time, end_time, max_capacity, is_free) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $this->trainer_id, 
            $this->class_name, 
            $this->class_date, 
            $this->start_time, 
            $this->end_time, 
            $this->max_capacity,
            $this->is_free // <--- SEND THE DATA TO DATABASE
        ]);
    }

    /**
     * Deletes a schedule
     */
    public static function delete($id) {
        $db = getDBConnection();
        $stmt = $db->prepare("DELETE FROM schedules WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>