<?php
// Prevent the session_start() warning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../Model/PaymentModel.php');

// ==========================================
// 4. DESIGN PATTERN: STRATEGY PATTERN
// ==========================================
interface PaymentStrategy {
    public function pay($amount);
}

class CreditCardPayment implements PaymentStrategy {
    public function pay($amount) { return true; /* Simulate success */ }
}

class FPXPayment implements PaymentStrategy {
    public function pay($amount) { return true; /* Simulate success */ }
}

class EwalletPayment implements PaymentStrategy {
    public function pay($amount) { return true; /* Simulate success */ }
}
// ==========================================

class PaymentController {
    private $model;

    public function __construct() {
        $this->model = new PaymentModel();
    }

    // Get all plans for the view
    public function getPlans() {
        return $this->model->getMembershipPlans();
    }

    // Process the checkout logic
    public function processCheckout($memberId, $planId, $amount, $method) {
        
        // 1. Utilize the Strategy Pattern based on user selection
        $processor = null;
        if ($method == 'Credit Card') {
            $processor = new CreditCardPayment();
        } else if ($method == 'Online Banking') {
            $processor = new FPXPayment();
        } else {
            $processor = new EwalletPayment();
        }

        // 2. Execute the payment strategy
        if ($processor->pay($amount)) {
            
            // 3. Get plan details
            $plan = $this->model->getPlanById($planId);
            $durationMonths = $plan['duration_months']; 

            // 4. Calculate new expiry date AND dynamically set the Payment Type
            $currentExpiry = $this->model->getUserExpiryDate($memberId);
            $today = new DateTime();
            $paymentType = ''; // We will set this below
            
            if (empty($currentExpiry)) {
                // Brand new user (Never had a membership)
                $newExpiry = $today->modify("+$durationMonths months")->format('Y-m-d');
                $paymentType = 'New Registration';
            } else if (new DateTime($currentExpiry) < $today) {
                // Completely expired membership
                $newExpiry = $today->modify("+$durationMonths months")->format('Y-m-d');
                $paymentType = 'Renewal / Upgrade';
            } else {
                // Add time to existing active membership
                $expiryDate = new DateTime($currentExpiry);
                $newExpiry = $expiryDate->modify("+$durationMonths months")->format('Y-m-d');
                $paymentType = 'Renewal / Upgrade';
            }

            // 5. Update user and save transaction
            $this->model->updateUserExpiryDate($memberId, $newExpiry);
            $_SESSION['membership_expiry_date'] = $newExpiry; // Update session

            // FIXED: Pass the dynamic $paymentType instead of the hardcoded 'Membership' string
            return $this->model->savePayment($memberId, $planId, $paymentType, $amount, $method);
        }
        
        return false;
    }
}
?>