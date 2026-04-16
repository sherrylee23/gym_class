<?php
require_once('../user_management/Database.php');

class BookingModel {
    /**
     * Requirement 2.2.2: Storing the record in the database
     * Creates a confirmed booking for a user.
     */
    public function createBooking($userId, $scheduleId) {
        $db = getDBConnection();
        $stmt = $db->prepare("INSERT INTO bookings (user_id, schedule_id, status) VALUES (?, ?, 'Confirmed')");
        return $stmt->execute([$userId, $scheduleId]);
    }

    /**
     * Requirement 2.2.2: Confirm member has not reserved the same class
     */
    public function checkUserDuplicate($userId, $scheduleId) {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND schedule_id = ? AND status = 'Confirmed'");
        $stmt->execute([$userId, $scheduleId]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Requirement 2.2.1: Determine existing reservations
     * Counts how many members have booked a specific slot.
     */
    public function getCurrentOccupancy($scheduleId) {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM bookings WHERE schedule_id = ? AND status = 'Confirmed'");
        $stmt->execute([$scheduleId]);
        return $stmt->fetchColumn();
    }

    /**
     * Requirement 2.2.4: View My Bookings
     * Retrieves all reservations for a specific member with full class and trainer details.
     * FIXED: Added s.end_time to ensure the view displays the full duration.
     */
    public function getMemberBookings($userId) {
        $db = getDBConnection();
        // JOINs are used to pull details from schedules and trainers tables
        $sql = "SELECT DISTINCT b.booking_id, b.status, s.class_name, s.class_date, s.start_time, s.end_time, t.full_name as trainer_name 
                FROM bookings b
                JOIN schedules s ON b.schedule_id = s.id
                LEFT JOIN trainers t ON s.trainer_id = t.id
                WHERE b.user_id = ?
                ORDER BY s.class_date DESC, s.start_time DESC";
                
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Requirement 2.2.3: Cancel Booking
     * Modifies the booking record in the database by updating status to 'Cancelled'.
     */
    public function cancelBooking($bookingId, $userId) {
        $db = getDBConnection();
        // Requirement 2.2.3: The system modifies the booking record in the database
        $stmt = $db->prepare("UPDATE bookings SET status = 'Cancelled' WHERE booking_id = ? AND user_id = ?");
        return $stmt->execute([$bookingId, $userId]);
    }

    /**
     * Requirement: Admin Manage Bookings
     * Joins bookings with users, schedules, and trainers for a complete overview.
     * FIXED: Added s.end_time for the admin dashboard consistency.
     */
    public function getAllBookings() {
        $db = getDBConnection();
        // Logic: Pull member name from users table and class details from schedules
        $sql = "SELECT DISTINCT b.booking_id, b.status, u.full_name as member_name, s.class_name, s.class_date, s.start_time, s.end_time, t.full_name as trainer_name 
                FROM bookings b
                JOIN users u ON b.user_id = u.id
                JOIN schedules s ON b.schedule_id = s.id
                LEFT JOIN trainers t ON s.trainer_id = t.id
                ORDER BY s.class_date DESC, s.start_time DESC";
                
        $stmt = $db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>