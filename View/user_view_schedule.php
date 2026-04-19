<?php
session_start();
require_once('../Model/Database.php');
require_once('../Model/Schedule.php');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=unauthorized");
    exit();
}

// Get current user ID for personal booking status
$currentUserId = $_SESSION['user_id'];

// Fetch Membership Status for UI Logic
$db = getDBConnection();
$stmt = $db->prepare("SELECT membership_expiry_date FROM users WHERE id = ?");
$stmt->execute([$currentUserId]);
$userExpiry = $stmt->fetchColumn();

$today = new DateTime();
$isUserExpired = (empty($userExpiry) || new DateTime($userExpiry) < $today);

try {
    // Returns available slots, user booked status, and time conflict
    $schedules = Schedule::getAll($currentUserId);
} catch (Exception $e) {
    $schedules = [];
    $error_msg = "Error loading schedules: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Available Classes - Anytime Fitness</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
        <style>
            body {
                background: #f8f9fa;
                font-family: 'Poppins', sans-serif;
            }
            .hero-header {
                background: linear-gradient(135deg, #6f42c1 0%, #4e2a84 100%);
                color: white;
                padding: 50px 0;
                margin-bottom: 40px;
                border-radius: 0 0 30px 30px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            }
            .filter-section {
                background: white;
                border-radius: 15px;
                padding: 20px;
                margin-top: -30px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.1);
                border: none;
            }
            .class-card {
                border: none;
                border-radius: 15px;
                background: white;
                transition: all 0.3s ease;
                box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            }
            .class-card:hover {
                transform: translateY(-10px);
                box-shadow: 0 12px 25px rgba(111, 66, 193, 0.15);
            }
            .btn-purple {
                background: #6f42c1;
                color: white;
                border-radius: 10px;
                font-weight: 600;
                padding: 10px 20px;
                transition: 0.3s;
            }
            .btn-purple:hover {
                background: #59359a;
                color: white;
            }
            .info-item {
                font-size: 0.9rem;
                color: #6c757d;
                margin-bottom: 8px;
            }
            .info-item i {
                color: #6f42c1;
                margin-right: 8px;
                width: 20px;
            }
            .class-name {
                color: #333;
                font-weight: 700;
                margin-bottom: 15px;
            }
            .text-purple {
                color: #6f42c1;
            }
            .slot-badge {
                font-size: 0.75rem;
                padding: 4px 8px;
                border-radius: 6px;
            }
        </style>
    </head>
    <body>

        <div class="hero-header text-center">
            <div class="container">
                <h1 class="display-5 fw-bold mt-2">Pick Your Perfect Class</h1>
                <p class="lead opacity-75">Your fitness journey starts with one click.</p>
                <div class="d-flex justify-content-center gap-2 mt-3">
                    <a href="../View/profile.php" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                    </a>
                    <a href="view_my_bookings.php" class="btn btn-light text-purple btn-sm fw-bold shadow-sm">
                        <i class="bi bi-calendar-check me-1"></i> View My Bookings
                    </a>
                </div>
            </div>
        </div>

        <div class="container mb-5">
            <div class="filter-section">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label fw-bold small text-muted text-uppercase">Search Class or Trainer</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-search text-purple"></i></span>
                            <input type="text" id="classSearch" class="form-control bg-light border-0" placeholder="e.g. Yoga, John Doe...">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-muted text-uppercase">Filter by Date</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-calendar-event text-purple"></i></span>
                            <input type="date" id="dateFilter" class="form-control bg-light border-0">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="container pb-5">

            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger text-center">
                    <?php echo htmlspecialchars($error_msg); ?>
                </div>
            <?php endif; ?>

            <div class="row g-4" id="scheduleContainer">
                <?php if (!empty($schedules)): ?>
                    <?php foreach ($schedules as $row): ?>
                        <?php
                        // if class have been fully book, it not display to user
                        if (isset($row['available_slots']) && $row['available_slots'] <= 0) {
                            continue;
                        }
                        ?>
                        <div class="col-lg-4 col-md-6 schedule-card-item" 
                             data-name="<?php echo strtolower(htmlspecialchars($row['class_name'])); ?>"
                             <div class="col-lg-4 col-md-6 schedule-card-item" 
                             <div class="col-lg-4 col-md-6 schedule-card-item" 
                                 data-name="<?php echo strtolower(htmlspecialchars($row['class_name'])); ?>"
                                 data-trainer="<?php echo strtolower(htmlspecialchars($row['trainer_name'])); ?>"
                                 data-specialty="<?php echo strtolower(htmlspecialchars($row['specialty'])); ?>" 
                                 data-date="<?php echo $row['class_date']; ?>">

                                    <div class="card class-card h-100 p-3">
                                        <div class="card-body d-flex flex-column">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h4 class="class-name mb-0"><?php echo htmlspecialchars($row['class_name']); ?></h4>
                                                <span class="badge slot-badge bg-info text-dark">
                                                    <i class="bi bi-people-fill me-1"></i><?php echo htmlspecialchars($row['available_slots']); ?> Left
                                                </span>
                                            </div>

                                            <div class="info-item">
                                                <i class="bi bi-person-badge"></i>
                                                <span>Trainer: <strong><?php echo htmlspecialchars($row['trainer_name'] ?: 'TBA'); ?></strong></span>

                                                <?php if (!empty($row['specialty'])): ?>
                                                    <span class="badge rounded-pill bg-light text-purple border border-purple-subtle ms-1" style="font-size: 0.7rem; color: #6f42c1;">
                                                        <i class="bi bi-patch-check-fill me-1"></i><?php echo $row['specialty']; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>

                                            <div class="info-item">
                                                <i class="bi bi-calendar3"></i>
                                                <span>Date: <?php echo date("D, d M Y", strtotime($row['class_date'])); ?></span>
                                            </div>

                                            <div class="info-item">
                                                <i class="bi bi-clock"></i>
                                                <span>Time: <?php echo date("H:i", strtotime($row['start_time'])) . " - " . date("H:i", strtotime($row['end_time'])); ?></span>
                                            </div>

                                            <div class="mt-auto pt-4">
                                                <?php
                                                // Use the Factory-generated label and the is_free flag from our Model
                                                $accessLabel = $row['access_type'];
                                                $isFree = (int) $row['is_free'];
                                                ?>

                                                <?php if ($row['trainer_id'] == $currentUserId): ?>
                                                    <button class="btn btn-outline-secondary w-100" disabled>
                                                        <i class="bi bi-person-badge-fill me-2"></i>YOU ARE THE TRAINER
                                                    </button>

                                                <?php elseif ($row['user_booked'] > 0): ?>
                                                    <button class="btn btn-secondary w-100" disabled>
                                                        <i class="bi bi-bookmark-check-fill me-2"></i>ALREADY BOOKED
                                                    </button>

                                                <?php elseif ($isFree === 0 && $isUserExpired): ?>
                                                    <a href="../View/viewPlans.php?error=must_pay_first" class="btn btn-outline-danger w-100 fw-bold">
                                                        <i class="bi bi-lock-fill me-2"></i><?php echo $accessLabel; ?>
                                                    </a>

                                                <?php elseif (isset($row['time_conflict']) && $row['time_conflict'] > 0): ?>
                                                    <button class="btn btn-outline-danger w-100" disabled>
                                                        <i class="bi bi-exclamation-triangle me-2"></i>TIME CONFLICT
                                                    </button>
                                                    <small class="text-danger d-block text-center mt-1" style="font-size: 0.7rem;">Overlaps with your schedule</small>

                                                <?php else: ?>
                                                    <a href="booking.php?id=<?php echo urlencode($row['id']); ?>" class="btn btn-purple w-100">
                                                        <i class="bi bi-check2-circle me-2"></i>Book Now (<?php echo $accessLabel; ?>)
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12 text-center py-5">
                                <div class="bg-white p-5 rounded-4 shadow-sm">
                                    <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                                    <p class="mt-3 text-muted">No classes with available slots are currently scheduled.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div id="noResults" class="text-center py-5 d-none">
                        <i class="bi bi-search text-muted" style="font-size: 3rem;"></i>
                        <p class="mt-3 text-muted">No classes match your search criteria.</p>
                    </div>
                </div>

                <script>
                    const classSearch = document.getElementById('classSearch');
                    const dateFilter = document.getElementById('dateFilter');
                    const cards = document.querySelectorAll('.schedule-card-item');
                    const noResults = document.getElementById('noResults');

                    function filterCards() {
                        const searchText = classSearch.value.toLowerCase();
                        const filterDate = dateFilter.value;
                        let visibleCount = 0;

                        cards.forEach(card => {
                            const name = card.getAttribute('data-name');
                            const trainer = card.getAttribute('data-trainer');
                            const date = card.getAttribute('data-date');

                            const matchesText = name.includes(searchText) || trainer.includes(searchText);
                            const matchesDate = filterDate === "" || date === filterDate;

                            if (matchesText && matchesDate) {
                                card.classList.remove('d-none');
                                visibleCount++;
                            } else {
                                card.classList.add('d-none');
                            }
                        });

                        if (visibleCount === 0) {
                            noResults.classList.remove('d-none');
                        } else {
                            noResults.classList.add('d-none');
                        }
                    }

                    classSearch.addEventListener('input', filterCards);
                    dateFilter.addEventListener('change', filterCards);

                    function clearFilters() {
                        classSearch.value = "";
                        dateFilter.value = "";
                        filterCards();
                    }
                </script>

                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
                </body>
                </html>
