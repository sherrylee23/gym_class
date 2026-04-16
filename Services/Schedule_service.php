<?php
require_once('../Model/Schedule.php');
require_once('../Model/Trainer.php');

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $schedules = Schedule::getAll();
    echo json_encode($schedules ?: []);
    exit();
}

if ($method === 'POST') {
    // delete class
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = $_POST['schedule_id'];
        if (Schedule::delete($id)) {
            echo json_encode(['status' => 'success', 'message' => 'Schedule deleted successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database error during deletion.']);
        }
        exit();
    }

    // add class
    if (isset($_POST['class_name'])) {
        $t_id  = $_POST['trainer_id'];
        $name  = $_POST['class_name'];
        $date  = $_POST['class_date'];
        $start = $_POST['start_time'];
        $end   = $_POST['end_time'];
        $max   = isset($_POST['max_capacity']) ? $_POST['max_capacity'] : 20;

        // check time validation
        if (strtotime($end) <= strtotime($start)) {
            echo json_encode(['status' => 'error', 'message' => 'End time must be after start time.']);
            exit();
        }

        // handle duplicate class and time
        if (Schedule::isConflict($name, $date, $start, $end)) {
            echo json_encode([
                'status' => 'error', 
                'message' => "Sorry, A $name class already exists at this time slot."
            ]);
            exit(); 
        } 
        
        // save
        $newSched = new Schedule();
        $newSched->trainer_id = $t_id;
        $newSched->class_name = $name;
        $newSched->class_date = $date;
        $newSched->start_time = $start;
        $newSched->end_time   = $end;
        $newSched->max_capacity = $max;
        
        $newSched->is_free = isset($_POST['is_free']) ? $_POST['is_free'] : 0;

        if ($newSched->save()) {
            echo json_encode(['status' => 'success', 'message' => 'Schedule added successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database error during save.']);
        }
        
        exit();
    }
}
?>