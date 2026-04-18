<?php
header('Content-Type: application/json');
require_once('../Model/UserFacade.php');

// Only allow GET request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        "status" => "F",
        "message" => "Invalid request method. Only GET is allowed.",
        "timeStamp" => date('Y-m-d H:i:s')
    ]);
    exit;
}

// Required IFA parameters
$requestID = $_GET['requestID'] ?? '';
$userId = $_GET['userId'] ?? '';
$timeStamp = $_GET['timeStamp'] ?? '';

// Basic validation
if (empty($requestID) || empty($userId) || empty($timeStamp)) {
    echo json_encode([
        "status" => "F",
        "message" => "Missing required parameters: requestID, userId, or timeStamp.",
        "timeStamp" => date('Y-m-d H:i:s')
    ]);
    exit;
}

if (!ctype_digit((string)$userId)) {
    echo json_encode([
        "status" => "F",
        "message" => "Invalid userId format.",
        "timeStamp" => date('Y-m-d H:i:s')
    ]);
    exit;
}

try {
    $facade = new UserFacade();
    $user = $facade->getUserById((int)$userId);

    if ($user) {
        echo json_encode([
            "status" => "S",
            "requestID" => $requestID,
            "userId" => $user['id'],
            "fullName" => $user['full_name'],
            "email" => $user['email'],
            "phoneNumber" => $user['phone_number'] ?? '',
            "role" => $user['role'],
            "timeStamp" => date('Y-m-d H:i:s')
        ]);
    } else {
        echo json_encode([
            "status" => "F",
            "requestID" => $requestID,
            "message" => "User not found.",
            "timeStamp" => date('Y-m-d H:i:s')
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        "status" => "E",
        "requestID" => $requestID,
        "message" => "System error occurred.",
        "timeStamp" => date('Y-m-d H:i:s')
    ]);
}
?>