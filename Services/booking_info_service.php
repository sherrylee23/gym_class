<?php
require_once('../user_management/Database.php');
require_once('../Model/BookingModel.php');

// Set header to JSON so it displays correctly in the browser
header('Content-Type: application/json');

// REQUIREMENT: API Key Security
$apiKey = $_GET['api_key'] ?? '';
$validKey = "GYM_BOOKING_API_2026";

if ($apiKey !== $validKey) {
    echo json_encode(['error' => 'Unauthorized: Invalid API Key.']);
    exit();
}

$bookingModel = new BookingModel();
$userId = $_GET['user_id'] ?? 0;

// Logic: If user_id is passed, show their bookings. Otherwise, show all.
if ($userId > 0) {
    $data = $bookingModel->getMemberBookings($userId);
} else {
    $data = $bookingModel->getAllBookings();
}

// This outputs the JSON string you see in the browser
echo json_encode($data ?: []);
exit();