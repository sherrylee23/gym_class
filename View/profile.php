<?php
// author: Cheok Jia Xuen

require_once('../Services/UserController.php');
require_once('../Model/Database.php');
require_once('../Services/user_booking_api_client.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$ctrl = new UserController();
$error_update = "";

// intial trainer speciality
$trainer_specialty = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $error_update = $ctrl->updateProfile($_POST);
}

$role = $_SESSION['role'];

$isExpired = true;
$statusText = "Inactive / Guest";
$statusClass = "bg-secondary";
$daysRemaining = 0;
$progressPercent = 0;
$barColor = "bg-success";
$expiryDate = null;

if ($role == 'Member') {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT membership_expiry_date FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $expiryDate = $stmt->fetchColumn();

    $today = new DateTime();

    if (!empty($expiryDate)) {
        $expiryObj = new DateTime($expiryDate);

        if ($expiryObj >= $today) {
            $isExpired = false;
            $statusText = "Active Member";
            $statusClass = "bg-success";

            $interval = $today->diff($expiryObj);
            $daysRemaining = $interval->days;

            $progressPercent = ($daysRemaining / 30) * 100;
            if ($progressPercent > 100) {
                $progressPercent = 100;
            }

            if ($daysRemaining <= 5) {
                $barColor = "bg-danger";
            } elseif ($daysRemaining <= 14) {
                $barColor = "bg-warning";
            }
        } else {
            $statusText = "Expired";
            $statusClass = "bg-danger";
        }
    }
}

// check trainer
if ($_SESSION['role'] === 'Trainer') {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT specialty FROM trainers WHERE trainer_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $trainer_specialty = $stmt->fetchColumn() ?: "";
}

// Consume booking module service
$bookingData = null;
if ($role === 'Member') {
    $bookingData = getBookingHistory($_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($role); ?> Dashboard - Anytime Fitness</title>
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
                overflow: hidden;
                background: white;
            }
            .header-purple {
                background: #6f42c1;
                color: white;
                padding: 25px;
                text-align: center;
            }
            .btn-purple {
                background: #6f42c1;
                color: white;
                border: none;
                transition: 0.3s;
            }
            .btn-purple:hover {
                background: #59359a;
                color: white;
                transform: translateY(-2px);
            }
            .info-box {
                background: #f8f9fa;
                border-radius: 15px;
                padding: 20px;
                border-left: 5px solid #6f42c1;
                margin-bottom: 20px;
            }
            .profile-label {
                font-size: 0.75rem;
                font-weight: 700;
                color: #6f42c1;
                text-transform: uppercase;
            }
            .logout-link {
                color: #dc3545;
                text-decoration: none;
                font-weight: 600;
                transition: 0.3s;
            }
            .logout-link:hover {
                color: #a71d2a;
            }
            .text-purple {
                color: #6f42c1;
            }
            .booking-card {
                border-radius: 15px;
                border: 1px solid #e9ecef;
                background: #fff;
            }
        </style>
    </head>
    <body>

        <div class="container">
            <div class="row g-4">

                <div class="col-md-8">
                    <div class="card h-100">
                        <div class="header-purple">
                            <i class="bi bi-lightning-charge-fill fs-2"></i>
                            <h2 class="h4 mb-0 fw-bold"><?php echo strtoupper(htmlspecialchars($role)); ?> DASHBOARD</h2>
                        </div>
                        <div class="card-body p-4 p-md-5">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-light p-3 rounded-circle me-3">
                                    <i class="bi bi-person-circle fs-1 text-purple"></i>
                                </div>
                                <div>
                                    <h3 class="fw-bold mb-0">Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h3>
                                    <p class="text-muted mb-0">Welcome to your Anytime Fitness portal.</p>
                                </div>
                            </div>

                            <div class="info-box">
                                <?php if ($role == 'Member'): ?>

                                    <div class="card border mb-4 shadow-sm" style="border-radius: 15px;">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <div>
                                                    <h6 class="text-muted mb-1 small fw-bold text-uppercase">Account Status</h6>
                                                    <span class="badge <?php echo htmlspecialchars($statusClass); ?> fs-6 px-3 py-2">
                                                        <i class="bi bi-patch-check-fill me-1"></i>
                                                        <?php echo htmlspecialchars($statusText); ?>
                                                    </span>
                                                </div>

                                                <?php if (!$isExpired): ?>
                                                    <div class="text-end">
                                                        <p class="mb-0 text-muted small fw-bold text-uppercase">Valid Until</p>
                                                        <p class="fw-bold mb-0 text-dark"><?php echo date('d M Y', strtotime($expiryDate)); ?></p>
                                                    </div>
                                                <?php else: ?>
                                                    <a href="viewPlans.php" class="btn btn-purple btn-sm px-4 fw-bold shadow-sm">Join Now</a>
                                                <?php endif; ?>
                                            </div>

                                            <?php if (!$isExpired): ?>
                                                <div class="mt-3">
                                                    <div class="d-flex justify-content-between small text-muted mb-1">
                                                        <span class="fw-bold">Time Remaining</span>
                                                        <span class="fw-bold <?php echo str_replace('bg-', 'text-', $barColor); ?>">
                                                            <?php echo (int) $daysRemaining; ?> Days Left
                                                        </span>
                                                    </div>
                                                    <div class="progress" style="height: 10px; border-radius: 10px; background-color: #e9ecef;">
                                                        <div class="progress-bar progress-bar-striped progress-bar-animated <?php echo htmlspecialchars($barColor); ?>"
                                                             role="progressbar"
                                                             style="width: <?php echo (float) $progressPercent; ?>%;"
                                                             aria-valuenow="<?php echo (float) $progressPercent; ?>"
                                                             aria-valuemin="0"
                                                             aria-valuemax="100">
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <h5 class="fw-bold text-purple"><i class="bi bi-activity me-2"></i>Available Services</h5>
                                    <p class="text-muted small">Access your personalized gym member tools here.</p>
                                    <div class="d-flex gap-2 mt-3 flex-wrap">
                                        <a href="user_view_schedule.php" class="btn btn-purple mt-2">
                                            <i class="bi bi-calendar-plus me-2"></i>Book Class
                                        </a>
                                        <a href="viewPlans.php" class="btn btn-outline-dark mt-2 fw-bold">
                                            <i class="bi bi-wallet2 me-2"></i>Pay Memberships
                                        </a>
                                        <a href="viewHistory.php" class="btn btn-outline-secondary mt-2">
                                            <i class="bi bi-clock-history me-2"></i>Payment History
                                        </a>
                                    </div>

                                    <div class="mt-4">
                                        <h6 class="fw-bold text-purple">Recent Booking Information</h6>

                                        <?php if (empty($bookingData)): ?>
                                            <p class="small text-muted mb-0">No recent booking found.</p>

                                        <?php elseif (isset($bookingData['status']) && $bookingData['status'] === 'E'): ?>
                                            <p class="small text-danger mb-0"><?php echo htmlspecialchars($bookingData['message']); ?></p>

                                        <?php else: ?>
                                            <?php foreach ($bookingData as $booking): ?>
                                                <div class="booking-card shadow-sm p-3 mt-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($booking['class_name'] ?? 'Class'); ?></h6>
                                                        <span class="badge bg-success">
                                                            <?php echo htmlspecialchars($booking['status'] ?? 'Confirmed'); ?>
                                                        </span>
                                                    </div>

                                                    <p class="mb-1 small text-muted">
                                                        <i class="bi bi-calendar-event me-2"></i>
                                                        <?php echo htmlspecialchars($booking['class_date'] ?? '-'); ?>
                                                    </p>

                                                    <p class="mb-1 small text-muted">
                                                        <i class="bi bi-clock me-2"></i>
                                                        <?php echo htmlspecialchars($booking['start_time'] ?? '-'); ?>
                                                        -
                                                        <?php echo htmlspecialchars($booking['end_time'] ?? '-'); ?>
                                                    </p>

                                                    <p class="mb-0 small text-muted">
                                                        <i class="bi bi-person-badge me-2"></i>
                                                        Trainer: <?php echo htmlspecialchars($booking['trainer_name'] ?? '-'); ?>
                                                    </p>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>

                                <?php elseif ($role == 'Admin'): ?>
                                    <h5 class="fw-bold text-purple"><i class="bi bi-shield-check me-2"></i>System Administration</h5>
                                    <p class="text-muted small">Manage users and oversee system reservations.</p>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="DisplayUsers.php" class="btn btn-purple mt-2">
                                            <i class="bi bi-people-fill me-1"></i> Manage All Users
                                        </a>
                                        <a href="../View/admin_manage_bookings.php" class="btn btn-outline-dark mt-2">
                                            <i class="bi bi-journal-check me-1"></i> Manage All Bookings
                                        </a>
                                    </div>

                                <?php elseif ($role == 'Trainer'): ?>
                                    <h5 class="fw-bold text-purple"><i class="bi bi-calendar3 me-2"></i>Training Schedule</h5>
                                    <p class="text-muted small">Your upcoming sessions and assigned members are listed here.</p>
                                    <a href="trainer_manage_schedule.php" class="btn btn-purple mt-2">
                                        <i class="bi bi-list-check me-2"></i>View Schedule
                                    </a>
                                <?php endif; ?>
                            </div>

                            <hr class="my-4">
                            <div class="text-center">
                                <a href="logout.php" class="logout-link">
                                    <i class="bi bi-box-arrow-left me-1"></i> LOGOUT FROM SYSTEM
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="p-4">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-gear-wide-connected fs-4 text-purple me-2"></i>
                                <h5 class="fw-bold mb-0">Account Settings</h5>
                            </div>

                            <?php if (isset($_GET['update']) && $_GET['update'] == 'success'): ?>
                                <div class="alert alert-success d-flex align-items-center py-2 small" role="alert">
                                    <i class="bi bi-check-circle-fill me-2"></i> Profile Updated!
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($error_update)): ?>
                                <div class="alert alert-danger py-2 small">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <?php echo htmlspecialchars($error_update); ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                                <div class="mb-3">
                                    <label class="profile-label">Full Name</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                        <input type="text" name="full_name" class="form-control bg-light border-start-0" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="profile-label">Phone Number (11 Digits)</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-phone text-muted"></i></span>
                                        <input
                                            type="text"
                                            name="phone_number"
                                            class="form-control bg-light border-start-0"
                                            placeholder="01234567890"
                                            required
                                            maxlength="11"
                                            pattern="\d{11}"
                                            title="Please enter exactly 11 digits"
                                            >
                                    </div>
                                    <div class="form-text mt-1" style="font-size: 0.65rem;">Must be 11 digits (e.g., 01234567890).</div>
                                </div>

                                <div class="mb-4">
                                    <label class="profile-label">Password</label>
                                    <?php if ($role === 'Trainer'): ?>
                                        <div class="mb-3">
                                            <label class="profile-label">My Specialty</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-star text-muted"></i></span>
                                                <select name="specialty" class="form-select bg-light border-start-0">
                                                    <option value="" disabled <?php echo empty($trainer_specialty) ? 'selected' : ''; ?>>-- Select Specialty --</option>
                                                    <option value="HIIT" <?php echo ($trainer_specialty == 'HIIT') ? 'selected' : ''; ?>>HIIT</option>
                                                    <option value="Dance" <?php echo ($trainer_specialty == 'Dance') ? 'selected' : ''; ?>>Dance</option>
                                                    <option value="Zumba" <?php echo ($trainer_specialty == 'Zumba') ? 'selected' : ''; ?>>Zumba</option>
                                                    <option value="Yoga" <?php echo ($trainer_specialty == 'Yoga') ? 'selected' : ''; ?>>Yoga</option>
                                                </select>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                                        <input type="password" name="password" class="form-control bg-light border-start-0" placeholder="New Password (optional)">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-purple w-100 fw-bold shadow-sm">
                                    <i class="bi bi-save me-2"></i>SAVE CHANGES
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>