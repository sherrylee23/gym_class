<?php
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

        // 1. Requirement 2.2.2: Check if member has reserved the same class before
        if ($this->realModel->checkUserDuplicate($userId, $scheduleId)) {
            return ["status" => "error", "message" => "You have already reserved this class."];
        }

        // --- NEW Logic: TIME CONFLICT CHECK ---
        // Fetch the details of the class the user is trying to book
        $stmt = $db->prepare("SELECT class_date, start_time, end_time FROM schedules WHERE id = ?");
        $stmt->execute([$scheduleId]);
        $requestedClass = $stmt->fetch();

        if (!$requestedClass) {
            return ["status" => "error", "message" => "Class schedule not found."];
        }

        // Check if user has any existing booking that overlaps with these times
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
        // --- END OF TIME CONFLICT CHECK ---

        // 2. Requirement 2.2.2: Determine if class reached maximum capacity
        $currentOccupancy = $this->realModel->getCurrentOccupancy($scheduleId);
        
        // Testing limit as requested (Max 2 for testing)
        $maxTestingLimit = 2; 

        if ($currentOccupancy >= $maxTestingLimit) {
            return ["status" => "error", "message" => "This class has reached its capacity (Max: 2)."];
        }

        // 3. Persistence: Save to SQL if all validations pass
        if ($this->realModel->createBooking($userId, $scheduleId)) {
            return ["status" => "success", "message" => "Booking confirmed!"];
        }

        return ["status" => "error", "message" => "Database error."];
    }

    /**
     * Requirement 2.2.3: Cancel Booking
     * Gatekeeper logic: Strictly prevents cancellation after class start time for ALL roles.
     */
    public function attemptCancellation($bookingId, $userId, $role = 'Member') {
        $db = getDBConnection();
        
        // Fetch booking info to verify ownership and timing
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

        // --- GLOBAL SECURITY RULE: TIME CHECK ---
        // This is outside the $role check, so it applies to both Member and Admin
        $classStart = strtotime($booking['class_date'] . ' ' . $booking['start_time']);
        if (time() >= $classStart) {
            return ["status" => "error", "message" => "Action Denied: The class has already started or passed."];
        }

        // --- ROLE SPECIFIC RULE: OWNERSHIP ---
        if ($role !== 'Admin') {
            // Members can only cancel their OWN bookings
            if ($booking['owner_id'] != $userId) {
                return ["status" => "error", "message" => "Unauthorized ownership."];
            }
        }

        // If time is valid and ownership (if applicable) is valid, perform the cancellation
        if ($this->realModel->cancelBooking($bookingId, $booking['owner_id'])) {
            return ["status" => "success", "message" => "Booking cancelled. Slot is now available."];
        }

        return ["status" => "error", "message" => "System error during cancellation."];
    }
}