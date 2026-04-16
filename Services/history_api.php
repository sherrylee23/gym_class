<?php
// File: Services/history_api.php
// This file acts as the API Provider

// Hide PHP warnings so they don't break the JSON output
error_reporting(0);
header('Content-Type: application/json');

require_once('../Model/PaymentModel.php');

// Check if member_id was passed in the URL
if (isset($_GET['member_id'])) {
    $member_id = intval($_GET['member_id']);
    
    $model = new PaymentModel();
    $transactions = $model->getPaymentHistory($member_id);
    
    // Return the data as a clean JSON object
    echo json_encode([
        'status' => 'success',
        'member_id' => $member_id,
        'transactions' => $transactions
    ]);

} else {
    // Return an error if no ID was provided
    echo json_encode([
        'status' => 'error',
        'message' => 'No member_id provided in the request.'
    ]);
}
?>