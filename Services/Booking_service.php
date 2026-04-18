<?php
session_start();
require_once('../Model/BookingModel.php');
require_once('../View/BookingProxy.php');
require_once('../Model/Database.php');

$proxy = new BookingProxy();
$bookingModel = new BookingModel();

// Get session values first
$userIdFromSession = $_SESSION['user_id'] ?? null;
$userRole = $_SESSION['role'] ?? null;

// Secure Coding: Ensure user is logged in
if (!$userIdFromSession) {
    header("Location: ../View/login.php?error=unauthorized");
    exit();
}

// --- HANDLE CANCELLATION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    $bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_SANITIZE_NUMBER_INT);

    $result = $proxy->attemptCancellation($bookingId, $userIdFromSession, $userRole);

    $redirect = ($userRole === 'Admin') ? '../View/admin_manage_bookings.php' : '../View/view_my_bookings.php';
    header("Location: $redirect?status=" . $result['status'] . "&message=" . urlencode($result['message']));
    exit();
}

// --- HANDLE NEW BOOKING ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['schedule_id'])) {
    $scheduleId = filter_input(INPUT_POST, 'schedule_id', FILTER_SANITIZE_NUMBER_INT);

    $db = getDBConnection();

    $classStmt = $db->prepare("SELECT is_free FROM schedules WHERE id = ?");
    $classStmt->execute([$scheduleId]);
    $isFreeClass = $classStmt->fetchColumn();

    $userStmt = $db->prepare("SELECT membership_expiry_date FROM users WHERE id = ?");
    $userStmt->execute([$userIdFromSession]);
    $expiryDate = $userStmt->fetchColumn();

    $today = new DateTime();

    if ($isFreeClass == 0 && (empty($expiryDate) || new DateTime($expiryDate) < $today)) {
        header("Location: ../View/viewPlans.php?error=must_pay_first");
        exit();
    }

    if ($bookingModel->checkUserDuplicate($userIdFromSession, $scheduleId)) {
        header("Location: ../View/user_view_schedule.php?status=error&message=You+have+already+booked+this+class!");
        exit();
    }

    $result = $proxy->attemptBooking($userIdFromSession, $scheduleId);

    header("Location: ../View/booking.php?id=$scheduleId&status=" . $result['status'] . "&message=" . urlencode($result['message']));
    exit();
}

header("Location: ../View/user_view_schedule.php?status=error&message=Invalid+Request");
exit();
?>