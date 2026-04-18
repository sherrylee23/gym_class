<?php
session_start();
require_once('../Services/PaymentController.php');

//2.4.1 View Membership Plans
//2.4.2 Make Payment (frontend)
// 1. If the session does not exist, immediately reject access
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$controller = new PaymentController();
$plans = $controller->getPlans();
$error_message = "";

// Ensure these variables exist for the button logic below
$currentExpiry = $_SESSION['membership_expiry_date'] ?? null; // Adjust if you store this differently
$today = new DateTime();

// Handle Form Submission
// 2. During POST requests, ignore form inputs and strictly use Server Session Data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $member_id = $_SESSION['user_id'];
    
    // ==========================================
    // INTEGRATION: ACTING AS A CONSUMER
    // ==========================================
    // 1. Reach out to User's Web Service
    $userServiceUrl = 'http://localhost/gym_class/Model/user_service.php';
    $userResponse = @file_get_contents($userServiceUrl); //acting like an external client asking for data.
    $usersList = json_decode($userResponse, true);

    // 2. Verify the member exists in his JSON data
    $isValidUser = false;
    if ($usersList && !isset($usersList['error'])) {
        foreach ($usersList as $user) {
            if ($user['id'] == $member_id) {
                $isValidUser = true;
                break;
            }
        }
    }

    // 3. Make a decision based on the consumed data
    if (!$isValidUser) {
        $error_message = "Payment Denied: Could not verify your account via the User Service API.";
    } else {
        // The user is verified! Process the payment.
        $isSuccess = $controller->processCheckout($member_id, $_POST['plan_id'], $_POST['amount'], $_POST['payment_method']);

        if ($isSuccess) {
            header('Location: viewHistory.php?payment=success');
            exit;
        } else {
            $error_message = "Database error processing payment.";
        }
    }
    // ==========================================
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
    <div>
        <a href="../View/profile.php" class="btn btn-outline-light mt-2 me-2">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
        
        <a href="viewHistory.php" class="btn btn-light mt-2">
            <i class="bi bi-clock-history"></i> View History
        </a>
    </div>
</div>

<div class="container">
    <?php if (isset($_GET['error']) && $_GET['error'] == 'must_pay_first'): ?>
        <div class="alert alert-danger alert-dismissible fade show text-center shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
            <strong>Action Denied:</strong> You must purchase or renew a membership plan before you can book classes!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
  
    <?php if ($error_message) echo "<div class='alert alert-danger'>$error_message</div>"; ?>
    <div class="row g-4 justify-content-center">
        <?php foreach ($plans as $plan): ?>
            <div class="col-md-4">
                <div class="card plan-card h-100">
                    <h4><?php echo htmlspecialchars($plan['name']); ?></h4>
                    <h2 class="text-purple mb-4">RM <?php echo number_format($plan['price'], 2); ?></h2>
                    
                    <?php if (empty($currentExpiry) || new DateTime($currentExpiry) < $today): ?>
                        <button type="button" class="btn btn-purple w-100 fw-bold mt-auto" data-bs-toggle="modal" data-bs-target="#checkoutModal<?php echo $plan['plan_id']; ?>">
                            Join Now
                        </button>
                    <?php else: ?>
                        <button type="button" class="btn btn-outline-purple border-2 w-100 fw-bold mt-auto" style="color: #6f42c1; border-color: #6f42c1;" data-bs-toggle="modal" data-bs-target="#checkoutModal<?php echo $plan['plan_id']; ?>">
                            Renew / Upgrade
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="modal fade" id="checkoutModal<?php echo $plan['plan_id']; ?>" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="border-radius: 15px;">
                        
                        <div class="modal-header bg-light">
                            <h5 class="modal-title fw-bold" id="checkoutModalLabel">
                                <i class="bi bi-cart-check-fill text-purple me-2"></i>Order Summary
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <form method="POST">
                            <div class="modal-body p-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Membership Tier:</span>
                                    <span class="fw-bold"><?php echo htmlspecialchars($plan['name']); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                                    <span class="text-muted">Duration:</span>
                                    <span class="fw-bold"><?php echo $plan['duration_months']; ?> Month(s)</span>
                                </div>
                                <div class="d-flex justify-content-between mb-4">
                                    <span class="fs-5 fw-bold">Total Due:</span>
                                    <span class="fs-5 fw-bold text-success">RM <?php echo number_format($plan['price'], 2); ?></span>
                                </div>

                                    <input type="hidden" name="plan_id" value="<?php echo $plan['plan_id']; ?>">
                                    <input type="hidden" name="amount" value="<?php echo $plan['price']; ?>">

                                    <label class="form-label fw-bold small text-muted text-uppercase">Select Payment Method</label>
                                    <select name="payment_method" class="form-select form-select-lg mb-3" required>
                                        <option value="" disabled selected>Choose a method...</option>
                                        <option value="Credit Card">💳 Credit / Debit Card</option>
                                        <option value="Online Banking">🏦 FPX Online Banking</option>
                                        <option value="E-Wallet">📱 E-Wallet TNG / GrabPay</option>
                                    </select>
                                </div>

                                <div class="modal-footer border-0 pb-4 px-4">
                                    <button type="button" class="btn btn-light w-100 mb-2" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-purple w-100 fw-bold fs-5 shadow-sm">Confirm & Pay</button>
                                </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
    </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Find all forms on the page
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            // Find the submit button inside the form that was just clicked
            const btn = this.querySelector('button[type="submit"]');
            
            // Change the text and add a Bootstrap spinner!
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...';
            
            // Disable the button so they can't click it again
            btn.classList.add('disabled');
            btn.style.pointerEvents = 'none';
        });
    });
</script>
</body>
</html>
