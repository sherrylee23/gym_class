<?php
session_start();
require_once('../user_management/Database.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Trainer') {
    header("Location: ../user_management/login.php?error=unauthorized");
    exit();
}

$scheduleId = $_GET['schedule_id'] ?? null;
if (empty($scheduleId) || !ctype_digit((string)$scheduleId)) {
    die("Invalid schedule ID.");
}

$db = getDBConnection();

// 1. Verify this class belongs to current trainer
$stmt = $db->prepare("
    SELECT s.*, t.full_name AS trainer_name
    FROM schedules s
    JOIN trainers t ON s.trainer_id = t.trainer_id
    WHERE s.id = ?
");
$stmt->execute([$scheduleId]);
$class = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$class) {
    die("Class not found.");
}

if ((int)$class['trainer_id'] !== (int)$_SESSION['user_id']) {
    die("Unauthorized access. This is not your class.");
}

/*
  2. Get all booked user IDs for this class

  Assumption:
  - bookings table has: booking_id, user_id, schedule_id, status
  If your teammate table uses another column name, just adjust this query.
*/
$stmt2 = $db->prepare("
    SELECT user_id, status
    FROM bookings
    WHERE schedule_id = ?
    ORDER BY user_id ASC
");
$stmt2->execute([$scheduleId]);
$bookingRows = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$bookedUsers = [];

foreach ($bookingRows as $row) {
    $userId = $row['user_id'];

    $requestID = 'TRAINER' . $scheduleId . '_' . $userId;
    $timeStamp = date('Y-m-d H:i:s');

    $url = "http://localhost/gym_class/user_management/getUserProfileapi.php"
        . "?requestID=" . urlencode($requestID)
        . "&userId=" . urlencode($userId)
        . "&timeStamp=" . urlencode($timeStamp);

    $response = @file_get_contents($url);

    if ($response !== false) {
        $userData = json_decode($response, true);

        if (isset($userData['status']) && $userData['status'] === 'S') {
            $bookedUsers[] = [
                'userId' => $userData['userId'],
                'fullName' => $userData['fullName'],
                'email' => $userData['email'],
                'phoneNumber' => $userData['phoneNumber'] ?? '',
                'role' => $userData['role'],
                'bookingStatus' => $row['status'] ?? ''
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booked Users - Anytime Fitness</title>
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
        .back-link {
            text-decoration: none;
            color: rgba(255,255,255,0.85);
            margin-bottom: 20px;
            display: inline-block;
        }
        .back-link:hover {
            color: white;
        }
        .text-purple {
            color: #6f42c1 !important;
        }
        .badge-soft {
            background: #f3ecff;
            color: #6f42c1;
            border: 1px solid #e3d6ff;
        }
    </style>
</head>
<body>

<div class="container">
    <a href="trainer_manage_schedule.php" class="back-link">
        <i class="bi bi-arrow-left me-1"></i> Back to Class Management
    </a>

    <div class="mb-4 text-white">
        <h2 class="fw-bold mb-1">Booked Users</h2>
        <p class="mb-0 opacity-75">
            <?php echo htmlspecialchars($class['class_name']); ?>
            •
            <?php echo htmlspecialchars($class['class_date']); ?>
            •
            <?php echo date("H:i", strtotime($class['start_time'])) . " - " . date("H:i", strtotime($class['end_time'])); ?>
        </p>
    </div>

    <div class="card p-4">
        <?php if (empty($bookedUsers)): ?>
            <div class="text-center py-5">
                <i class="bi bi-people fs-1 text-muted"></i>
                <p class="mt-3 mb-0 text-muted">No users have booked this class yet.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone Number</th>
                        <th>Role</th>
                        <th>Booking Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($bookedUsers as $user): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($user['userId']); ?></td>
                            <td class="fw-bold"><?php echo htmlspecialchars($user['fullName']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phoneNumber']); ?></td>
                            <td>
                                <span class="badge badge-soft"><?php echo htmlspecialchars($user['role']); ?></span>
                            </td>
                            <td>
                                <span class="badge bg-success"><?php echo htmlspecialchars($user['bookingStatus']); ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>