<?php
require_once('BookingRecord.php');

class BookingModel {

    public function createBooking($userId, $scheduleId) {
        $booking = new BookingRecord();
        $booking->fill(['user_id' => $userId, 'schedule_id' => $scheduleId, 'status' => 'Confirmed']);
        return $booking->save();
    }

    public function cancelBooking($bookingId, $userId) {
        // Use the public BaseModel::db() method
        $stmt = BaseModel::db()->prepare("SELECT * FROM bookings WHERE booking_id = ? AND user_id = ?");
        $stmt->execute([$bookingId, $userId]);
        
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            $booking = new BookingRecord();
            $booking->setBookingId($bookingId);
            return $booking->cancel();
        }
        return false;
    }

    public function checkUserDuplicate($userId, $scheduleId) {
        $stmt = BaseModel::db()->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND schedule_id = ? AND status = 'Confirmed'");
        $stmt->execute([$userId, $scheduleId]);
        return $stmt->fetchColumn() > 0;
    }

    public function getCurrentOccupancy($scheduleId) {
        $stmt = BaseModel::db()->prepare("SELECT COUNT(*) FROM bookings WHERE schedule_id = ? AND status = 'Confirmed'");
        $stmt->execute([$scheduleId]);
        return $stmt->fetchColumn();
    }

    public function getMemberBookings($userId) {
        $sql = "SELECT DISTINCT b.booking_id, b.status, s.class_name, s.class_date, s.start_time, s.end_time, t.full_name as trainer_name 
                FROM bookings b
                JOIN schedules s ON b.schedule_id = s.id
                LEFT JOIN trainers t ON s.trainer_id = t.trainer_id
                WHERE b.user_id = ?
                ORDER BY s.class_date DESC, s.start_time DESC";
        $stmt = BaseModel::db()->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllBookings() {
        $sql = "SELECT DISTINCT b.booking_id, b.status, u.full_name as member_name, s.class_name, s.class_date, s.start_time, s.end_time, t.full_name as trainer_name 
                FROM bookings b
                JOIN users u ON b.user_id = u.id
                JOIN schedules s ON b.schedule_id = s.id
                LEFT JOIN trainers t ON s.trainer_id = t.trainer_id
                ORDER BY s.class_date DESC, s.start_time DESC";
        $stmt = BaseModel::db()->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}