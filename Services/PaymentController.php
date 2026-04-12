<?php
require_once('../Model/PaymentModel.php');;

// --- YOUR STRATEGY DESIGN PATTERN ---
interface PaymentStrategy {
    public function pay($amount);
}

class CreditCardPayment implements PaymentStrategy {
    public function pay($amount) {
        // Logic for Stripe/Bank API would go here
        return true; 
    }
}

class FPXPayment implements PaymentStrategy {
    public function pay($amount) {
        // Logic for FPX API would go here
        return true; 
    }
}
// --- END STRATEGY PATTERN ---

class PaymentController {
    private $model;

    public function __construct() {
        $this->model = new PaymentModel();
    }

    // 1. Get Plans for the View
    public function getPlans() {
        return $this->model->getMembershipPlans();
    }

    // 2. Process Checkout Logic
    public function processCheckout($memberId, $planId, $amount, $method) {
        // Execute Strategy Pattern
        $processor = ($method === 'Credit Card') ? new CreditCardPayment() : new FPXPayment();
        $processor->pay($amount);

        // Tell Model to save to database
        return $this->model->savePayment($memberId, $planId, $amount, $method);
    }

    // 3. Generate JSON API format for Web Services Requirement
    public function generateHistoryAPI($memberId) {
        $history = $this->model->getPaymentHistory($memberId);
        $timestamp = date('Y-m-d H:i:s');

        if (empty($history)) {
            return ['status' => 'F', 'message' => 'No payment history', 'timeStamp' => $timestamp];
        }

        return ['status' => 'S', 'transactions' => $history, 'timeStamp' => $timestamp];
    }
}
?>