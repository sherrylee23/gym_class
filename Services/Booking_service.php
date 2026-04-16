<?php
session_start();
// Pointing to the Proxy located in the View folder
require_once('../View/BookingProxy.php');
require_once('../user_management/Database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $proxy = new BookingProxy();
    
    // Extract both ID and Role to handle Admin Overrides (Requirement 2.2.3)
    $userIdFromSession = $_SESSION['user_id'] ?? null;
    $userRole = $_SESSION['role'] ?? null;

    // Secure Coding: Ensure user is logged in
    if (!$userIdFromSession) {
        header("Location: ../user_management/login.php?error=unauthorized");
        exit();
    }

    // --- HANDLE CANCELLATION (Requirement 2.2.3) ---
    if (isset($_POST['action']) && $_POST['action'] === 'cancel') {
        $bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_SANITIZE_NUMBER_INT);
        
        // Pass userRole so the Proxy can bypass the "time check" for Admins
        $result = $proxy->attemptCancellation($bookingId, $userIdFromSession, $userRole);
        
        // Redirect Logic: Return to the appropriate dashboard based on role
        if ($userRole === 'Admin') {
            header("Location: ../View/admin_manage_bookings.php?status=" . $result['status'] . "&message=" . urlencode($result['message']));
        } else {
            header("Location: ../View/view_my_bookings.php?status=" . $result['status'] . "&message=" . urlencode($result['message']));
        }
        exit();
    }

    // --- HANDLE NEW BOOKING (Requirement 2.2.2) ---
    $scheduleId = filter_input(INPUT_POST, 'schedule_id', FILTER_SANITIZE_NUMBER_INT);

    if ($scheduleId) {
        
        // ==========================================
        // INTEGRATION: SMART PAYMENT GATEKEEPER 
        // ==========================================
        $db = getDBConnection(); 

        // 1. First, check if the specific class they clicked is a "Free" class
        $classStmt = $db->prepare("SELECT is_free FROM schedules WHERE id = ?");
        $classStmt->execute([$scheduleId]);
        $isFreeClass = $classStmt->fetchColumn();

        // 2. Second, fetch the user's expiry date
        $stmt = $db->prepare("SELECT membership_expiry_date FROM users WHERE id = ?");
        $stmt->execute([$userIdFromSession]);
        $expiryDate = $stmt->fetchColumn();

        $today = new DateTime();

        // 3. The Logic Check: 
        // IF the class is NOT free (0) AND the user's membership is expired/empty... THEN block them!
        if ($isFreeClass == 0 && (empty($expiryDate) || new DateTime($expiryDate) < $today)) {
            
            // BLOCKED! This is a premium class. Kick them to the payment page.
            header("Location: ../View/viewPlans.php?error=must_pay_first");
            exit(); 
        }

        if ($isFreeClass == 0 && (empty($expiryDate) || new DateTime($expiryDate) < $today)) {
            // BLOCKED! This is a premium class. Kick them to the payment page.
            header("Location: ../View/viewPlans.php?error=must_pay_first");
            exit(); 
        }
        
        $checkStmt = $db->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND schedule_id = ? AND status = 'Confirmed'");
        $checkStmt->execute([$userIdFromSession, $scheduleId]);
        $alreadyBooked = $checkStmt->fetchColumn();

        if ($alreadyBooked > 0) {
            // Kick them back to the schedule page immediately!
            header("Location: ../View/user_view_schedule.php?status=error&message=You+have+already+booked+this+class!");
            exit();
        }

        // If they passed the Gatekeeper AND the Duplicate Shield...
        // Proxy enforces capacity (Max 2 for testing)
        $result = $proxy->attemptBooking($userIdFromSession, $scheduleId);
        
        // Redirect back to the booking confirmation page with status
        header("Location: ../View/booking.php?id=$scheduleId&status=" . $result['status'] . "&message=" . urlencode($result['message']));
    } else {
        header("Location: ../View/user_view_schedule.php?status=error&message=Invalid+Request");
    }
    exit();
}
?>
}