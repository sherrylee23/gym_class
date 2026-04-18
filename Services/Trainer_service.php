<?php
require_once('../Model/Database.php');

header('Content-Type: application/json');

// web service logic
if (isset($_GET['id'])) {
    // get a trainer
    $trainer = Trainer::findById($_GET['id']);
    echo json_encode($trainer ?: ['error' => 'Trainer not found']);
} else {
    // get trainer list
    $trainers = Trainer::getAll();
    echo json_encode($trainers ?: ['error' => 'No trainers available']);
}
?>