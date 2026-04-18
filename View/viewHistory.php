<?php

//2.4.4 View Management History
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$member_id = $_SESSION['user_id'];
$request_id = uniqid("REQ_");

$url = 'http://localhost/gym_class/Services/history_api.php?member_id=' . $member_id. '&requestID=' . $request_id;

//  PHP will yell us if the link is broken!
$response = file_get_contents($url);

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
    <div>
        <a href="../View/profile.php" class="btn btn-outline-light mt-2 me-2">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
        
        <a href="viewPlans.php" class="btn btn-light mt-2">
            <i class="bi bi-card-list"></i> View Plans
        </a>
    </div>
</div>

<div class="container">
    
    <?php if (isset($_GET['payment']) && $_GET['payment'] == 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i> 
            <strong>Payment Successful!</strong> Your new membership plan has been activated and your receipt is below.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <div class="card p-4 shadow-sm border-0" style="border-radius: 15px;">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Type</th> <th>Plan</th>
                    <th>Amount</th>
                    <th>Method</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($transactions)): ?>
                    <?php foreach ($transactions as $t): ?>
                        <tr>
                            <td class="text-muted small"><?php echo date('d M Y, h:i A', strtotime($t['payment_date'])); ?></td>
                            
                            <td>
                                <span class="badge bg-info text-dark">
                                    <?php echo htmlspecialchars($t['payment_type'] ?? 'Registration'); ?>
                                </span>
                            </td>

                            <td class="fw-bold"><?php echo htmlspecialchars($t['plan_name']); ?></td>
                            <td class="text-success fw-bold">RM <?php echo number_format($t['amount'], 2); ?></td>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($t['payment_method']); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No payment history found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
