<?php
session_start();
require_once('../Model/Schedule.php');
require_once('../user_management/Database.php');

// Secure Coding: Verify member session
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Member') {
    header("Location: ../user_management/login.php?error=unauthorized");
    exit();
}

$class_id = $_GET['id'] ?? null;
if (!$class_id) {
    header("Location: user_view_schedule.php");
    exit();
}

// Fetch class info to display to the user
$db = getDBConnection();
$stmt = $db->prepare("SELECT s.*, t.full_name as trainer_name FROM schedules s JOIN trainers t ON s.trainer_id = t.id WHERE s.id = ?");
$stmt->execute([$class_id]);
$class = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$class) {
    die("Class not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Confirm Booking - Anytime Fitness</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
        <style>
            body {
                background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                font-family: 'Poppins', sans-serif;
            }
            .booking-card {
                border-radius: 20px;
                border: none;
                box-shadow: 0 15px 35px rgba(0,0,0,0.2);
                background: white;
            }
            .btn-purple {
                background: #6f42c1;
                color: white;
                border-radius: 10px;
                font-weight: 600;
                transition: 0.3s;
            }
            .btn-purple:hover {
                background: #59359a;
                transform: translateY(-2px);
                color: white;
            }
            .info-box {
                background: #f8f9fa;
                border-radius: 15px;
                padding: 20px;
                border-left: 5px solid #6f42c1;
            }
        </style>
    </head>
    <body>

        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card booking-card p-4">
                        <div class="text-center mb-4">
                            <i class="bi bi-calendar-check text-primary" style="font-size: 3rem;"></i>
                            <h2 class="fw-bold mt-2">Confirm Booking</h2>
                            <p class="text-muted">Please review class details below</p>
                        </div>

                        <div class="info-box mb-4">
                            <h5 class="fw-bold text-dark mb-3"><?php echo htmlspecialchars($class['class_name']); ?></h5>
                            <div class="mb-2 small"><i class="bi bi-person-badge me-2 text-primary"></i>Trainer: <strong><?php echo htmlspecialchars($class['trainer_name']); ?></strong></div>
                            <div class="mb-2 small"><i class="bi bi-calendar3 me-2 text-primary"></i>Date: <?php echo date("D, d M Y", strtotime($class['class_date'])); ?></div>
                            <div class="mb-0 small"><i class="bi bi-clock me-2 text-primary"></i>Time: <?php echo $class['start_time']; ?> - <?php echo $class['end_time']; ?></div>
                        </div>

                        <div id="responseMessage"></div>

                        <form action="../Services/Booking_service.php" method="POST">
                            <input type="hidden" name="schedule_id" value="<?php echo $class['id']; ?>">

                            <button type="submit" class="btn btn-purple w-100 py-3 mb-3">
                                <i class="bi bi-check-circle me-2"></i>CONFIRM RESERVATION
                            </button>

                            <a href="user_view_schedule.php" class="btn btn-outline-secondary w-100">Go Back</a>
                        </form>

                        <?php if (isset($_GET['status'])): ?>
                            <div class="alert alert-<?php echo ($_GET['status'] == 'success') ? 'success' : 'danger'; ?> mt-3">
                                <?php echo htmlspecialchars($_GET['message']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>


    </body>
</html>