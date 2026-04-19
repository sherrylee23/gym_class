<?php
// author: Koh Zhi Qian

require_once('../Model/Database.php');
require_once('../Model/BookingModel.php');

// Set header to JSON so it displays correctly in the browser 
header("Content-Type: application/json");

$bookingModel = new BookingModel();
$userId = $_GET['user_id'] ?? '';

// Simplified Logic: If 'admin_view_all' is passed, show everything.
// If a number is passed, show that user's bookings.
if ($userId === 'admin_view_all') {
    $data = $bookingModel->getAllBookings();
} elseif (is_numeric($userId) && (int)$userId > 0) {
    $data = $bookingModel->getMemberBookings((int)$userId);
} else {
    $data = []; // Return empty if no valid user_id is provided
}

// Output result in JSON format [cite: 209]
echo json_encode($data ?: []);
exit();