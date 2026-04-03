<?php
require_once('Database.php');
header('Content-Type: application/json'); // [cite: 73]

function getMembers() {
    $conn = getDBConnection();
    $stmt = $conn->query("SELECT id, full_name, email, role FROM users"); // [cite: 72]
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$data = getMembers();
echo json_encode($data ?: ['error' => 'No users found']); // [cite: 74, 75]
?>