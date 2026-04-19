<?php
// author: Koh Zhi Qian


session_start();
require_once('../Model/BookingModel.php');

// 1. Security Check: Only Admins can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../Model/login.php?error=unauthorized");
    exit();
}

/**
 * SECURE CODING PRACTICE: Least Privilege Principle
 * Consuming the Web Service instead of direct DB access ensures the Admin
 * interacts with the data through a controlled interface.
 */
function fetchAllBookingsForAdmin() {
    $url = "http://localhost/gym_class/Services/booking_info_service.php?user_id=admin_view_all";
    
    // Fetch JSON data from the service URL [cite: 220]
    $response = @file_get_contents($url);
    
    if ($response === false) {
        return [];
    }
    
    // Turn the JSON string into a PHP array [cite: 221]
    return json_decode($response, true);
}

// Fetch global data via the API
$allBookings = fetchAllBookingsForAdmin();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Management - Anytime Fitness</title>
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
        .management-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            border: none;
            overflow: hidden;
        }
        .header-section {
            background: #6f42c1;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .table thead {
            background-color: #f8f9fa;
        }
        .table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            color: #6f42c1;
            border: none;
        }
        .booking-row:hover {
            background-color: #f1f0ff;
            transition: 0.3s;
        }
        .btn-back {
            color: white;
            text-decoration: none;
            font-size: 0.9rem;
            transition: 0.3s;
            display: inline-block;
            margin-bottom: 15px;
        }
        .btn-back:hover {
            color: #d1d1d1;
        }
        .badge-confirmed { background-color: #198754; color: white; }
        .badge-cancelled { background-color: #6c757d; color: white; }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-11">

            <div class="mb-3">
                <a href="../View/profile.php" class="btn-back">
                    <i class="bi bi-arrow-left-circle me-1"></i> Back to Dashboard
                </a>
            </div>

            <div class="card management-card">
                <div class="header-section">
                    <i class="bi bi-journal-check fs-1 mb-2"></i>
                    <h1 class="h3 fw-bold mb-0">BOOKING MANAGEMENT</h1>
                    <p class="mb-0 small opacity-75">Overview and force-cancellation of member reservations</p>
                </div>

                <div class="card-body p-4">
                    <?php if (isset($_GET['status'])): ?>
                        <div class="alert alert-<?php echo ($_GET['status'] == 'success') ? 'success' : 'danger'; ?> py-2 small mb-3">
                            <i class="bi bi-info-circle me-2"></i> <?php echo htmlspecialchars($_GET['message'] ?? 'Action Processed', ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th class="ps-4">Member Name</th>
                                    <th>Class & Trainer</th>
                                    <th>Date & Time</th>
                                    <th>Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($allBookings)): ?>
                                    <?php foreach ($allBookings as $b): ?>
                                        <?php 
                                            // Time Logic Check
                                            $classStartTime = strtotime($b['class_date'] . ' ' . $b['start_time']);
                                            $currentTime = time();
                                            $isStartedOrPast = ($currentTime >= $classStartTime);
                                        ?>
                                        <tr class="booking-row">
                                            <td class="ps-4">
                                                <div class="fw-bold text-primary"><?php echo htmlspecialchars($b['member_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                            </td>
                                            <td>
                                                <div class="fw-bold"><?php echo htmlspecialchars($b['class_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                <small class="text-muted">with <?php echo htmlspecialchars($b['trainer_name'], ENT_QUOTES, 'UTF-8'); ?></small>
                                            </td>
                                            <td>
                                                <div class="small fw-bold"><?php echo date("d M Y", strtotime($b['class_date'])); ?></div>
                                                <div class="small text-muted"><?php echo $b['start_time']; ?> - <?php echo $b['end_time']; ?></div>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo ($b['status'] == 'Confirmed') ? 'badge-confirmed' : 'badge-cancelled'; ?> rounded-pill px-3">
                                                    <?php echo htmlspecialchars($b['status'], ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($b['status'] === 'Cancelled'): ?>
                                                    <i class="bi bi-dash-circle text-muted" title="Already Cancelled"></i>
                                                <?php elseif ($isStartedOrPast): ?>
                                                    <span class="badge bg-light text-danger border border-danger small">Started / Ended</span>
                                                <?php else: ?>
                                                    <form action="../Services/Booking_service.php" method="POST" onsubmit="return confirm('Admin: Force cancel this booking?');">
                                                        <input type="hidden" name="action" value="cancel">
                                                        <input type="hidden" name="booking_id" value="<?php echo $b['booking_id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="bi bi-x-circle me-1"></i> Cancel
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                            No bookings found in the system.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-light p-3 text-center">
                    <small class="text-muted">Total Registered Reservations: <?php echo count($allBookings); ?></small>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>