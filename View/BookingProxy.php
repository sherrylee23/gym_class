<?php
// author: Koh Zhi Qian


// Start session (THIS IS THE ROOT CAUSE)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fix: Point to the correct folder location
require_once('../Model/BookingModel.php');
require_once('../Model/Schedule.php');

class BookingProxy {

    private $realModel;

    public function __construct() {
        $this->realModel = new BookingModel();
    }

    /**
     * Requirement 2.2.2: Book a Class
     * Logic for reserving a class spot with capacity, duplicate, and time conflict checks.
     */
    public function attemptBooking($userId, $scheduleId) {
        // Secure Coding: Session and Role Validation
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Member') {
            return ["status" => "error", "message" => "Unauthorized access."];
        }

        $db = getDBConnection();

        // 1. Requirement 2.2.2: Check duplicate booking
        if ($this->realModel->checkUserDuplicate($userId, $scheduleId)) {
            return ["status" => "error", "message" => "You have already reserved this class."];
        }

        // --- TIME CONFLICT CHECK ---
        $stmt = $db->prepare("SELECT class_date, start_time, end_time FROM schedules WHERE id = ?");
        $stmt->execute([$scheduleId]);
        $requestedClass = $stmt->fetch();

        if (!$requestedClass) {
            return ["status" => "error", "message" => "Class schedule not found."];
        }

        $sqlConflict = "SELECT COUNT(*) FROM bookings b 
                        JOIN schedules s ON b.schedule_id = s.id 
                        WHERE b.user_id = ? 
                        AND b.status = 'Confirmed' 
                        AND s.class_date = ? 
                        AND (? < s.end_time AND ? > s.start_time)";

        $stmtConflict = $db->prepare($sqlConflict);
        $stmtConflict->execute([
            $userId,
            $requestedClass['class_date'],
            $requestedClass['start_time'],
            $requestedClass['end_time']
        ]);

        if ($stmtConflict->fetchColumn() > 0) {
            return ["status" => "error", "message" => "Time Conflict! You already have a class booked during this time slot."];
        }
        // --- END ---

        // 2. Capacity check
        $currentOccupancy = $this->realModel->getCurrentOccupancy($scheduleId);

        $stmt = $db->prepare("SELECT max_capacity FROM schedules WHERE id = ?");
        $stmt->execute([$scheduleId]);
        $maxCapacity = $stmt->fetchColumn();

        if ($currentOccupancy >= $maxCapacity) {
            return [
                "status" => "error",
                "message" => "This class has reached its capacity (Max: $maxCapacity)."
            ];
        }

        // 3. Save booking
        if ($this->realModel->createBooking($userId, $scheduleId)) {
            return ["status" => "success", "message" => "Booking confirmed!"];
        }

        return ["status" => "error", "message" => "Database error."];
    }

    /**
     * Cancel Booking
     */
    public function attemptCancellation($bookingId, $userId, $role = 'Member') {
        $db = getDBConnection();

        $sql = "SELECT b.user_id as owner_id, s.class_date, s.start_time 
                FROM bookings b 
                JOIN schedules s ON b.schedule_id = s.id 
                WHERE b.booking_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$bookingId]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            return ["status" => "error", "message" => "Booking not found."];
        }

        $classStart = strtotime($booking['class_date'] . ' ' . $booking['start_time']);
        if (time() >= $classStart) {
            return ["status" => "error", "message" => "Action Denied: The class has already started or passed."];
        }

        if ($role !== 'Admin') {
            if ($booking['owner_id'] != $userId) {
                return ["status" => "error", "message" => "Access Denied: You do not have permission to modify this booking."];
            }
        }

        if ($this->realModel->cancelBooking($bookingId, $booking['owner_id'])) {
            return ["status" => "success", "message" => "Booking cancelled. Slot is now available."];
        }

        return ["status" => "error", "message" => "System error during cancellation."];
    }
}