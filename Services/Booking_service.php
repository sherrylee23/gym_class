<?php
session_start();
// Pointing to the Proxy located in the View folder
require_once('../View/BookingProxy.php'); 

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
        // Proxy enforces capacity (Max 2 for testing) and duplicate checks
        $result = $proxy->attemptBooking($userIdFromSession, $scheduleId);
        
        // Redirect back to the booking confirmation page with status
        header("Location: ../View/booking.php?id=$scheduleId&status=" . $result['status'] . "&message=" . urlencode($result['message']));
    } else {
        header("Location: ../View/user_view_schedule.php?status=error&message=Invalid+Request");
    }
    exit();
}