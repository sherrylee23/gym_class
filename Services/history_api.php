<?php
require_once('PaymentController.php');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!isset($_GET['member_id'])) {
        echo json_encode(['status' => 'E', 'message' => 'Member ID required', 'timeStamp' => date('Y-m-d H:i:s')]);
        exit;
    }

    $controller = new PaymentController();
    $response = $controller->generateHistoryAPI($_GET['member_id']);
    
    echo json_encode($response);
}
?>