<?php
require_once('../user_management/Database.php');

class PaymentModel {
    private $db;

    public function __construct() {
        $this->db = getDBConnection(); // Assuming your Database.php is in this folder
    }

    // Fetch all membership plans
    public function getMembershipPlans() {
        $stmt = $this->db->query("SELECT * FROM membership_plans");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Save a new payment record
    public function savePayment($memberId, $planId, $amount, $method) {
        $stmt = $this->db->prepare("INSERT INTO payments (member_id, plan_id, amount, payment_method) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$memberId, $planId, $amount, $method]);
    }

    // Fetch a user's payment history
    public function getPaymentHistory($memberId) {
        $stmt = $this->db->prepare("
            SELECT p.id, p.amount, p.payment_method, p.payment_date, m.name as plan_name 
            FROM payments p 
            LEFT JOIN membership_plans m ON p.plan_id = m.id 
            WHERE p.member_id = ? 
            ORDER BY p.payment_date DESC
        ");
        $stmt->execute([$memberId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>