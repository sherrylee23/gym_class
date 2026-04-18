<?php

function getBookingHistory($userId) {
    $url = "http://localhost/gym_class/Services/booking_info_service.php?user_id=" . urlencode($userId);

    $response = @file_get_contents($url);

    if ($response === false) {
        return null;
    }

    $decoded = json_decode($response, true);

    if ($decoded === null) {
        return null;
    }

    return $decoded;
}
?>