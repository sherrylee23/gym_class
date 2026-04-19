<?php

require_once('Database.php');
require_once('AccessFactory.php');

class Schedule {

    public $id;
    public $trainer_id;
    public $class_name;
    public $class_date;
    public $start_time;
    public $end_time;
    public $max_capacity;
    public $is_free;

    // security: sensitiv data exposure mitigation --> using array_map to clean the data and factory add dynamic labels
    public static function getAll($currentUserId = 0) {
        $db = getDBConnection();
        $currentDate = date('Y-m-d');
        $currentTime = date('H:i:s');

        $sql = "SELECT s.*, t.full_name as trainer_name, t.specialty, 
        (SELECT COUNT(*) FROM bookings b WHERE b.schedule_id = s.id AND b.status = 'Confirmed') as booked_count,
        (SELECT COUNT(*) FROM bookings b3 WHERE b3.schedule_id = s.id AND b3.user_id = ? AND b3.status = 'Confirmed') as user_booked,
        (SELECT COUNT(*) FROM bookings b4 
         JOIN schedules s2 ON b4.schedule_id = s2.id 
         WHERE b4.user_id = ? AND b4.status = 'Confirmed' AND s2.class_date = s.class_date
         AND (s.start_time < s2.end_time AND s.end_time > s2.start_time)
        ) as time_conflict
        FROM schedules s 
        LEFT JOIN trainers t ON s.trainer_id = t.trainer_id
        WHERE (s.class_date > ? OR (s.class_date = ? AND s.start_time > ?))
        GROUP BY s.id ORDER BY s.class_date, s.start_time";

        $stmt = $db->prepare($sql);
        $stmt->execute([$currentUserId, $currentUserId, $currentDate, $currentDate, $currentTime]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function ($item) {
            $access = AccessFactory::create($item['is_free']);

            $calc_available = $item['max_capacity'] - $item['booked_count'];

            return [
                'id' => $item['id'],
                'trainer_id' => $item['trainer_id'],
                'class_name' => htmlspecialchars($item['class_name']),
                'trainer_name' => htmlspecialchars($item['trainer_name']),
                'specialty' => htmlspecialchars($item['specialty'] ?? 'General'),
                'class_date' => $item['class_date'],
                'start_time' => $item['start_time'],
                'end_time' => $item['end_time'],
                'max_capacity' => $item['max_capacity'],
                'booked_count' => $item['booked_count'],
                'available_slots' => $calc_available,
                'is_free' => (int) $item['is_free'],
                'access_type' => $access->getLabel(),
                'user_booked' => $item['user_booked'],
                'time_conflict' => $item['time_conflict']
            ];
        }, $results);
    }

    // security: mass assignment protection --> ensures attackers cannot inject hidden fields into database
    // only the require fill will be save
    public function safeFill(array $data) {
        $allowed = ['trainer_id', 'class_name', 'class_date', 'start_time', 'end_time', 'max_capacity', 'is_free'];
        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $this->$field = $data[$field];
            }
        }
    }

    // check class name and date cannot be duplicate and the end time cannot be less than start time
    public static function isConflict($name, $date, $start, $end) {
        $db = getDBConnection();
        $sql = "SELECT COUNT(*) FROM schedules WHERE class_name = ? AND class_date = ? AND (start_time < ? AND end_time > ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$name, $date, $end, $start]);
        return $stmt->fetchColumn() > 0;
    }

    public function save() {
        $db = getDBConnection();

        // --- 新增逻辑：只有 Yoga 允许为 Free (1)，其他强制为 Premium (0) ---
        if ($this->class_name !== 'Yoga') {
            $this->is_free = 0;
        }
        // ---------------------------------------------------------

        $sql = "INSERT INTO schedules (trainer_id, class_name, class_date, start_time, end_time, max_capacity, is_free) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
                    $this->trainer_id,
                    $this->class_name,
                    $this->class_date,
                    $this->start_time,
                    $this->end_time,
                    $this->max_capacity,
                    $this->is_free
        ]);
    }

    public static function delete($id) {
        $db = getDBConnection();
        $stmt = $db->prepare("DELETE FROM schedules WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
