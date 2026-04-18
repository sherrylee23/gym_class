<?php
require_once('../Model/Schedule.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Trainer') {
    header("Location: ../Model/login.php?error=unauthorized");
    exit();
}

// delete
$error_msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $url = "http://localhost/gym_class/Services/Schedule_service.php";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $_POST);
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error_msg = "Connection Error: " . curl_error($ch);
    } else {
        $result = json_decode($response);
        if (isset($result->status) && $result->status == "success") {
            header("Location: trainer_manage_schedule.php?msg=deleted");
            exit();
        } else {
            $error_msg = isset($result->message) ? $result->message : "API Error occurred.";
        }
    }
    curl_close($ch);
}

// get class list via web service
$serviceUrl = "http://localhost/gym_class/Services/Schedule_service.php";
$ch = curl_init($serviceUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$schedules = json_decode($response, true) ?: [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Schedule - Anytime Fitness</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        .card {
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            background: white;
            overflow: hidden;
        }
        .text-purple {
            color: #6f42c1 !important;
        }
        .btn-purple {
            background: #6f42c1;
            color: white;
            border-radius: 10px;
            padding: 10px 20px;
            transition: 0.3s;
        }
        .btn-purple:hover {
            background: #59359a;
            color: white;
            transform: translateY(-2px);
        }
        .back-link {
            text-decoration: none;
            color: rgba(255,255,255,0.8);
            margin-bottom: 20px;
            display: inline-block;
        }
        .back-link:hover {
            color: white;
        }
    </style>
</head>
<body>

<div class="container">
    <a href="../View/profile.php" class="back-link">
        <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
    </a>

    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
        <div class="alert alert-success border-0 shadow-sm mb-4">
            <i class="bi bi-check-circle me-2"></i>Schedule deleted successfully!
        </div>
    <?php endif; ?>

    <?php if ($error_msg): ?>
        <div class="alert alert-danger border-0 shadow-sm mb-4"><?php echo htmlspecialchars($error_msg); ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-end mb-4 text-white">
        <div>
            <h2 class="fw-bold mb-0">Class Management</h2>
            <p class="opacity-75 mb-0">View and manage all gym sessions</p>
        </div>
        <a href="trainer_add_schedule.php" class="btn btn-light text-purple fw-bold shadow-sm">
            <i class="bi bi-plus-lg me-2"></i>Create New Class
        </a>
    </div>

    <div class="card p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                <tr>
                    <th>Class Details</th>
                    <th>Trainer</th>
                    <th>Date & Time</th>
                    <th class="text-center">Action</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($schedules)): ?>
                    <?php foreach ($schedules as $s): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($s['class_name']); ?></div>
                                <div class="text-muted small">
                                    <i class="bi bi-people me-1"></i>Cap: <?php echo htmlspecialchars($s['max_capacity']); ?>
                                </div>
                            </td>
                            <td>
                                <span class="small"><?php echo htmlspecialchars($s['trainer_name']); ?></span>
                            </td>
                            <td>
                                <div class="small fw-bold text-purple"><?php echo htmlspecialchars($s['class_date']); ?></div>
                                <div class="badge bg-light text-dark border fw-normal">
                                    <?php echo date("H:i", strtotime($s['start_time'])); ?> - <?php echo date("H:i", strtotime($s['end_time'])); ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <?php if ($s['trainer_id'] == $_SESSION['user_id']): ?>
                                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                                        <a href="view_booked_users.php?schedule_id=<?php echo urlencode($s['id']); ?>"
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-people me-1"></i>View Users
                                        </a>

                                        <form action="trainer_manage_schedule.php" method="POST" onsubmit="return confirm('Delete this class?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="schedule_id" value="<?php echo htmlspecialchars($s['id']); ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-trash3 me-1"></i>Delete
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <i class="bi bi-lock-fill text-muted" title="Not your class"></i>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center py-5">No classes scheduled yet.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>