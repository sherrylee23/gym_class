<?php
require_once('BaseModel.php');

class BookingRecord extends BaseModel {
    protected $booking_id;
    protected $user_id;
    protected $schedule_id;
    protected $status;

    // Mass Assignment Protection
    public function fill(array $data) {
        $allowed = ['user_id', 'schedule_id', 'status'];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowed)) {
                $setter = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
                if (method_exists($this, $setter)) {
                    $this->$setter($value);
                }
            }
        }
    }

    // --- Setters with Validation ---
    public function setUserId($val) { $this->user_id = (int)$val; }
    public function setScheduleId($val) { $this->schedule_id = (int)$val; }
    // ADD THIS SETTER
    public function setBookingId($val) { $this->booking_id = (int)$val; }
    
    public function setStatus($val) { 
        $valid = ['Confirmed', 'Cancelled'];
        $this->status = in_array($val, $valid) ? $val : 'Confirmed'; 
    }

    // --- Getters ---
    public function getBookingId() { return $this->booking_id; }
    public function getStatus() { return $this->status; }

    // --- Database Actions ---
    public function save() {
        $stmt = self::db()->prepare("INSERT INTO bookings (user_id, schedule_id, status) VALUES (?, ?, ?)");
        return $stmt->execute([$this->user_id, $this->schedule_id, $this->status ?? 'Confirmed']);
    }

    public function cancel() {
        // Now this works because setBookingId() set the protected $booking_id property
        $stmt = self::db()->prepare("UPDATE bookings SET status = 'Cancelled' WHERE booking_id = ?");
        return $stmt->execute([$this->booking_id]);
    }
}