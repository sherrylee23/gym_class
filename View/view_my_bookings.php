<?php
session_start();

// Secure Coding: Ensure user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Member') {
    header("Location: login.php");
    exit();
}

$currentUserId = $_SESSION['user_id'];
// REQUIREMENT: Secret Key matching your Service Provider
$myBookingKey = "GYM_BOOKING_API_2026"; 

/**
 * Requirement 2.2.4: Consuming the Booking Module's Web Service via URL
 * Demonstrates linking your module info via URL instead of local file includes.
 */
function fetchMyBookingsViaService($userId, $key) {
    // URL pointing to the Provider logic in your Services folder
    // Attach the user_id to the URL!
    $url = "http://localhost/gym_class/Services/booking_info_service.php?api_key=GYM_BOOKING_API_2026&user_id=" . $userId;
    
    // Integrative Programming: Fetch JSON data from the service URL
    $response = @file_get_contents($url);
    
    if ($response === false) {
        return ['error' => 'Integration Error: Could not connect to the Booking Web Service.'];
    }
    
    return json_decode($response, true);
}

// Consume the web service logic
$myBookings = fetchMyBookingsViaService($currentUserId, $myBookingKey);

// Handle error messages from the service (e.g., unauthorized access)
if (isset($myBookings['error'])) {
    $error_msg = $myBookings['error'];
    $myBookings = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Reservations - Anytime Fitness</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #f8f9fa; font-family: 'Poppins', sans-serif; }
        .booking-header { background: #6f42c1; color: white; padding: 40px 0; margin-bottom: 30px; border-radius: 0 0 20px 20px; }
        .table-card { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .status-badge { font-size: 0.75rem; padding: 5px 12px; border-radius: 20px; }
    </style>
</head>
<body>

<div class="booking-header text-center">
    <div class="container">
        <h2 class="fw-bold"><i class="bi bi-journal-check me-2"></i>My Class Bookings</h2>
        <p class="small opacity-75">Linked via Integrative Booking Web Service</p>
        <a href="user_view_schedule.php" class="btn btn-sm btn-outline-light mt-2">
            <i class="bi bi-plus-circle me-1"></i> Book More Classes
        </a>
    </div>
</div>

<div class="container pb-5">
    <?php if (isset($_GET['message'])): ?>
        <div class="alert alert-<?php echo ($_GET['status'] == 'success') ? 'success' : 'danger'; ?> alert-dismissible fade show shadow-sm mb-4" role="alert">
            <i class="bi <?php echo ($_GET['status'] == 'success') ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; ?> me-2"></i>
            <?php echo htmlspecialchars($_GET['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error_msg)): ?>
        <div class="alert alert-warning shadow-sm border-0"><i class="bi bi-cloud-slash me-2"></i><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <div class="card table-card">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Class Info</th>
                            <th>Trainer</th>
                            <th>Date & Time</th>
                            <th class="text-center">Action / Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($myBookings) && !isset($myBookings['error'])): ?>
                            <?php foreach ($myBookings as $booking): ?>
                                <?php 
                                    // Logic to check if the class has already started or ended
                                    $classStartTime = strtotime($booking['class_date'] . ' ' . $booking['start_time']);
                                    $currentTime = time();
                                    $isStartedOrPast = ($currentTime >= $classStartTime);
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($booking['class_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($booking['trainer_name']); ?></td>
                                    <td>
                                        <div class="small fw-bold"><?php echo date("D, d M Y", strtotime($booking['class_date'])); ?></div>
                                        <div class="small text-muted"><?php echo $booking['start_time'] . " - " . $booking['end_time']; ?></div>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($booking['status'] === 'Cancelled'): ?>
                                            <span class="badge status-badge bg-secondary opacity-75">
                                                <i class="bi bi-slash-circle me-1"></i>Cancelled
                                            </span>

                                        <?php elseif ($isStartedOrPast): ?>
                                            <span class="badge status-badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i>Class Started/Ended
                                            </span>

                                        <?php else: ?>
                                            <form action="../Services/Booking_service.php" method="POST" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                                <input type="hidden" name="action" value="cancel">
                                                <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger shadow-sm">
                                                    <i class="bi bi-x-circle me-1"></i> Cancel Booking
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <i class="bi bi-calendar-x d-block fs-1 text-muted mb-3"></i>
                                    <p class="text-muted">No reservations found in the web service.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>