<?php
require_once('../Model/Database.php');

header('Content-Type: application/json');

function getMembers() {
    try {
        $conn = getDBConnection();
        $stmt = $conn->query("SELECT id, full_name, email, role FROM users");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $users ?: [];
    } catch (PDOException $e) {
        return ['error' => 'Database error'];
    }
}

$data = getMembers();
echo json_encode($data);
?>