<?php
session_start();
require_once('../Model/Schedule.php');
require_once('../Model/Database.php');

$userId = $_SESSION['user_id'] ?? $_SESSION['id'] ?? $_SESSION['member_id'] ?? null;


// 1. Verify user is logged in
if (!$userId) {
    header("Location: ../View/login.php?error=unauthorized");
    exit();
}

// 2. Validate the Class ID
$class_id = $_GET['id'] ?? null;
if (empty($class_id) || $class_id == 0) {
    die("Error: Invalid Class ID. Please go back to the schedule and try again.");
}

// 3. Fetch Class Data
$db = getDBConnection();
$stmt = $db->prepare("SELECT s.*, t.full_name as trainer_name FROM schedules s JOIN trainers t ON s.trainer_id = t.trainer_id WHERE s.id = ?");$stmt->execute([$class_id]);
$class = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$class) {
    die("Error: Class not found in the database.");
}

// 4. INTEGRATION: "ONLY YOGA IS FREE" GATEKEEPER 
$isYoga = (strtolower(trim($class['class_name'])) === 'yoga');

$stmt2 = $db->prepare("SELECT membership_expiry_date FROM users WHERE id = ?");
$stmt2->execute([$_SESSION['user_id']]);
$expiryDate = $stmt2->fetchColumn();
$today = new DateTime();

$isUserExpired = (empty($expiryDate) || new DateTime($expiryDate) < $today);

// If it's NOT Yoga, and the user is expired -> Kick them to payment
if (!$isYoga && $isUserExpired) {
    header("Location: ../View/viewPlans.php?error=must_pay_first");
    exit(); 
}

// If they pass the Gatekeeper, show them the Booking Form below!
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
                            <i class="bi bi-calendar-check text-primary" style="font-size: 3rem; color: #6f42c1 !important;"></i>
                            <h2 class="fw-bold mt-2">Confirm Booking</h2>
                            <p class="text-muted">Please review class details below</p>
                        </div>

                        <div class="info-box mb-4">
                            <h5 class="fw-bold text-dark mb-3"><?php echo htmlspecialchars($class['class_name']); ?></h5>
                            <div class="mb-2 small"><i class="bi bi-person-badge me-2" style="color: #6f42c1;"></i>Trainer: <strong><?php echo htmlspecialchars($class['trainer_name']); ?></strong></div>
                            <div class="mb-2 small"><i class="bi bi-calendar3 me-2" style="color: #6f42c1;"></i>Date: <?php echo date("D, d M Y", strtotime($class['class_date'])); ?></div>
                            <div class="mb-0 small"><i class="bi bi-clock me-2" style="color: #6f42c1;"></i>Time: <?php echo date("H:i", strtotime($class['start_time'])) . " - " . date("H:i", strtotime($class['end_time'])); ?></div>
                        </div>

                        <div id="responseMessage"></div>

                        <form action="../Services/Booking_service.php" method="POST" onsubmit="this.querySelector('button[type=submit]').disabled = true; this.querySelector('button[type=submit]').innerHTML = '<i class=\'bi bi-hourglass-split me-2\'></i>PROCESSING...';">
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