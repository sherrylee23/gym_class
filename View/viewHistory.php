<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../user_management/login.php");
    exit;
}

$member_id = $_SESSION['user_id'];

// Consuming your own API to show you know how to fetch JSON
$url = 'http://localhost/gym_class/payment_management/history_api.php?member_id=' . $member_id;
$response = @file_get_contents($url);
$historyData = json_decode($response, true);
$transactions = isset($historyData['transactions']) ? $historyData['transactions'] : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #f8f9fa; font-family: sans-serif; }
        .hero-header { background: linear-gradient(135deg, #6f42c1 0%, #4e2a84 100%); color: white; padding: 40px 0; margin-bottom: 30px; border-radius: 0 0 30px 30px; }
    </style>
</head>
<body>

<div class="hero-header text-center">
    <h2>Payment History</h2>
    <a href="viewPlans.php" class="btn btn-light mt-2">Back to Plans</a>
</div>

<div class="container">
    <div class="card p-4">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Plan</th>
                    <th>Amount</th>
                    <th>Method</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $t): ?>
                    <tr>
                        <td><?php echo date('d M Y', strtotime($t['payment_date'])); ?></td>
                        <td><?php echo htmlspecialchars($t['plan_name']); ?></td>
                        <td>RM <?php echo number_format($t['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($t['payment_method']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>