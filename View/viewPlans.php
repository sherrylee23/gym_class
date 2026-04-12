<?php
session_start();
require_once('../Services/PaymentController.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../user_management/login.php");
    exit;
}

$controller = new PaymentController();
$plans = $controller->getPlans();
$error_message = "";

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $member_id = $_SESSION['user_id'];
    $isSuccess = $controller->processCheckout($member_id, $_POST['plan_id'], $_POST['amount'], $_POST['payment_method']);

    if ($isSuccess) {
        header('Location: viewHistory.php?payment=success');
        exit;
    } else {
        $error_message = "Database error processing payment.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Membership Plans</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #f8f9fa; font-family: sans-serif; }
        .hero-header { background: linear-gradient(135deg, #6f42c1 0%, #4e2a84 100%); color: white; padding: 50px 0; margin-bottom: 40px; border-radius: 0 0 30px 30px; }
        .plan-card { border: none; border-radius: 15px; text-align: center; padding: 30px 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .btn-purple { background: #6f42c1; color: white; }
    </style>
</head>
<body>

<div class="hero-header text-center">
    <h2>Membership Plans</h2>
    <a href="viewHistory.php" class="btn btn-light mt-2">View History</a>
</div>

<div class="container">
    <?php if ($error_message) echo "<div class='alert alert-danger'>$error_message</div>"; ?>
    <div class="row g-4 justify-content-center">
        <?php foreach ($plans as $plan): ?>
            <div class="col-md-4">
                <div class="card plan-card">
                    <h4><?php echo htmlspecialchars($plan['name']); ?></h4>
                    <h2 class="text-purple">RM <?php echo $plan['price']; ?></h2>
                    <form method="POST" class="mt-3">
                        <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                        <input type="hidden" name="amount" value="<?php echo $plan['price']; ?>">
                        <select name="payment_method" class="form-select mb-3" required>
                            <option value="Credit Card">Credit Card</option>
                            <option value="Online Banking">FPX Online Banking</option>
                        </select>
                        <button type="submit" class="btn btn-purple w-100">Pay Now</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>