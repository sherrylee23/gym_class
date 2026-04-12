<?php
require_once('UserController.php');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$ctrl = new UserController();
$error_update = "";

// Handle Profile Update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $error_update = $ctrl->updateProfile($_POST);
}

$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $role; ?> Dashboard - Anytime Fitness</title>
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
        </style>
    </head>
    <body>

        <div class="container">
            <div class="row g-4">

                <div class="col-md-8">
                    <div class="card h-100">
                        <div class="header-purple">
                            <i class="bi bi-lightning-charge-fill fs-2"></i>
                            <h2 class="h4 mb-0 fw-bold"><?php echo strtoupper($role); ?> DASHBOARD</h2>
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
                                    <h5 class="fw-bold text-purple"><i class="bi bi-activity me-2"></i>Available Services</h5>
                                    <p class="text-muted small">Access your personalized gym member tools here.</p>
                                    <div class="d-flex gap-2 mt-3">
                                        <a href="/gym_class/View/user_view_schedule.php" class="btn btn-purple mt-2">
                                            <i class="bi bi-calendar-plus me-2"></i>Book Class
                                        </a>
                                        <button class="btn btn-outline-dark"><i class="bi bi-wallet2 me-2"></i>Pay Fees</button>
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
                                    <a href="/gym_class/View/trainer_manage_schedule.php" class="btn btn-purple mt-2">
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
                                    <?php echo $error_update; ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST">
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