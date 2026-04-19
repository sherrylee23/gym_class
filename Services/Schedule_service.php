<?php
// author: Lee Xin Ying

require_once('../Model/Schedule.php');
require_once('../Model/Trainer.php');

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

// get: expose data (api)
if ($method === 'GET') {
    if (isset($_GET['fetch']) && $_GET['fetch'] === 'trainers') {
        echo json_encode(Trainer::getAll());
        exit();
    }

    if (isset($_GET['trainer_id'])) {
        echo json_encode(Trainer::findById($_GET['trainer_id']) ?: ['error' => 'Not found']);
        exit();
    }

    // return all schedules (protected)
    echo json_encode(Schedule::getAll());
    exit();
}

// post: manage actions
if ($method === 'POST') {
    // Delete Action
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        if (Schedule::delete($_POST['schedule_id'])) {
            echo json_encode(['status' => 'success', 'message' => 'Deleted!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed.']);
        }
        exit();
    }

    // Add Action
    if (isset($_POST['class_name'])) {
        $newSched = new Schedule();

        // Apply Mass Assignment Security
        $newSched->safeFill($_POST);

        // date time validation
        $currentDateTime = time(); // Get current timestamp
        $inputDateTime = strtotime($newSched->class_date . ' ' . $newSched->start_time);

        if ($inputDateTime < $currentDateTime) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error: This time slot alrady pass, please create a future time slot. Thanks'
            ]);
            exit();
        }
        // logic
        if (empty($newSched->max_capacity))
            $newSched->max_capacity = 20;
        if (!isset($_POST['is_free']))
            $newSched->is_free = 0;

        // Validation for start and end duration
        if (strtotime($newSched->end_time) <= strtotime($newSched->start_time)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid time range: End time must be after start time.']);
            exit();
        }

        // Check for double-booking --> conflicts
        if (Schedule::isConflict($newSched->class_name, $newSched->class_date, $newSched->start_time, $newSched->end_time)) {
            echo json_encode(['status' => 'error', 'message' => 'Sorry, this time slot have been schedule to another trainers.']);
            exit();
        }

        if ($newSched->save()) {
            echo json_encode(['status' => 'success', 'message' => 'Schedule saved successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database error: Could not save schedule.']);
        }
        exit();
    }
}