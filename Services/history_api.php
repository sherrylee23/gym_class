<?php
// author: Loh Yee Kee

// File: Services/history_api.php
error_reporting(0);
header('Content-Type: application/json');
require_once('../Model/PaymentModel.php');

// Mandatory requirement: capture timestamp for the response
$responseTimestamp = date('Y-m-d H:i:s');

// Check for mandatory request parameters (member_id AND requestID)
if (isset($_GET['member_id']) && isset($_GET['requestID'])) {
    $member_id = intval($_GET['member_id']);
    $request_id = htmlspecialchars($_GET['requestID']);
    
    $model = new PaymentModel();
    $transactions = $model->getPaymentHistory($member_id);
    
    echo json_encode([
        'status' => 'S', // Success
        'requestID' => $request_id,
        'timeStamp' => $responseTimestamp,
        'member_id' => $member_id,
        'transactions' => $transactions
    ]);
} else {
    echo json_encode([
        'status' => 'E', // Error
        'timeStamp' => $responseTimestamp,
        'message' => 'Mandatory parameters missing: member_id and/or requestID.'
    ]);
}
?>
