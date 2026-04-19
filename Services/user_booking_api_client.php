<?php
// author: Koh Zhi Qian


function getBookingHistory($userId) {
    $url = "http://localhost/gym_class/Services/booking_info_service.php?user_id=" . urlencode($userId);

    $response = @file_get_contents($url);

    if ($response === false) {
        return [
            'status' => 'error',
            'message' => 'Unable to connect to booking_info_service.php'
        ];
    }

    $decoded = json_decode($response, true);

    if ($decoded === null) {
        return [
            'status' => 'error',
            'message' => 'Invalid JSON response from booking_info_service.php'
        ];
    }

    return $decoded;
}
?>