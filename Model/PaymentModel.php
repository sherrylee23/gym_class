<?php
// author: Loh Yee Kee

require_once('Database.php');

//2.4.1 View Membership Plans
class PaymentModel {
    private $db;

    public function __construct() {
        $this->db = getDBConnection(); // Assuming your Database.php is in this folder
    }
    
    // 1. Get details of the plan (FIXED: Uses plan_id AND returns array, NOT an entity object)
    public function getPlanById($planId) {
        $stmt = $this->db->prepare("SELECT * FROM membership_plans WHERE plan_id = ?");
        $stmt->execute([$planId]);
        return $stmt->fetch(PDO::FETCH_ASSOC); 
    }
    
    // 2. Check when the user's current membership expires
    public function getUserExpiryDate($memberId) {
        $stmt = $this->db->prepare("SELECT membership_expiry_date FROM users WHERE id = ?");
        $stmt->execute([$memberId]);
        return $stmt->fetchColumn();
    }
    
    // 3. Save the new calculated expiry date
    public function updateUserExpiryDate($memberId, $newDate) {
        $stmt = $this->db->prepare("UPDATE users SET membership_expiry_date = ? WHERE id = ?");
        return $stmt->execute([$newDate, $memberId]);
    }

    // Fetch all membership plans
    public function getMembershipPlans() {
        $stmt = $this->db->query("SELECT * FROM membership_plans");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Save a new payment record
    public function savePayment($memberId, $planId, $paymentType, $amount, $method) {
        $stmt = $this->db->prepare("INSERT INTO payments (member_id, plan_id, payment_type, "
                . "amount, payment_method) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$memberId, $planId, $paymentType, $amount, $method]);
    }

    // Fetch a user's payment history (FIXED: Joined on m.plan_id)
    public function getPaymentHistory($memberId) {
        $stmt = $this->db->prepare("
            SELECT p.id, p.payment_type, p.amount, p.payment_method, p.payment_date, m.name as plan_name 
            FROM payments p 
            LEFT JOIN membership_plans m ON p.plan_id = m.plan_id 
            WHERE p.member_id = ? 
            ORDER BY p.payment_date DESC
        ");
        $stmt->execute([$memberId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
