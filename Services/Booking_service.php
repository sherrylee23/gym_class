<?php
session_start();
require_once('../Model/BookingModel.php');
require_once('../View/BookingProxy.php');
require_once('../user_management/Database.php');

$proxy = new BookingProxy();
$bookingModel = new BookingModel();

// 1. Secure Coding: Ensure user is logged in
$userIdFromSession = $_SESSION['user_id'] ?? null;
$userRole = $_SESSION['role'] ?? null;

if (!$userIdFromSession) {
    header("Location: ../user_management/login.php?error=unauthorized");
    exit();
}

// --- HANDLE CANCELLATION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    $bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_SANITIZE_NUMBER_INT);
    
    // Proxy handles the security logic
    $result = $proxy->attemptCancellation($bookingId, $userIdFromSession, $userRole);
    
    $redirect = ($userRole === 'Admin') ? '../View/admin_manage_bookings.php' : '../View/view_my_bookings.php';
    header("Location: $redirect?status=" . $result['status'] . "&message=" . urlencode($result['message']));
    exit();
}

// --- HANDLE NEW BOOKING ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['schedule_id'])) {
    $scheduleId = filter_input(INPUT_POST, 'schedule_id', FILTER_SANITIZE_NUMBER_INT);
    
    // A. Membership Gatekeeper (Business Logic)
    $db = getDBConnection(); 
    $classStmt = $db->prepare("SELECT is_free FROM schedules WHERE id = ?");
    $classStmt->execute([$scheduleId]);
    $isFreeClass = $classStmt->fetchColumn();

    $userStmt = $db->prepare("SELECT membership_expiry_date FROM users WHERE id = ?");
    $userStmt->execute([$userIdFromSession]);
    $expiryDate = $userStmt->fetchColumn();
    $today = new DateTime();

    // Block if premium class and membership expired
    if ($isFreeClass == 0 && (empty($expiryDate) || new DateTime($expiryDate) < $today)) {
        header("Location: ../View/viewPlans.php?error=must_pay_first");
        exit(); 
    }

    // B. Duplicate Shield (Delegated to BookingModel)
    if ($bookingModel->checkUserDuplicate($userIdFromSession, $scheduleId)) {
        header("Location: ../View/user_view_schedule.php?status=error&message=You+have+already+booked+this+class!");
        exit();
    }

    // C. Proxy enforces capacity and creates the booking
    $result = $proxy->attemptBooking($userIdFromSession, $scheduleId);
    
    header("Location: ../View/booking.php?id=$scheduleId&status=" . $result['status'] . "&message=" . urlencode($result['message']));
    exit();
}

// Fallback for invalid requests
header("Location: ../View/user_view_schedule.php?status=error&message=Invalid+Request");
exit();
?>