<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Trainer') {
    header("Location: ../user_management/login.php?error=unauthorized");
    exit();
}

require_once('../Model/Trainer.php');
$trainers = Trainer::getAll();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Assign Class - Anytime Fitness</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

        <style>
            body {
                font-family: 'Poppins', sans-serif;
                background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                padding: 40px 0;
            }
            .card {
                border-radius: 20px;
                border: none;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                background: white;
                overflow: hidden;
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
                padding: 12px;
                font-weight: 600;
                border-radius: 10px;
                transition: 0.3s;
            }
            .btn-purple:hover {
                background: #59359a;
                transform: translateY(-2px);
            }
            .form-label {
                font-size: 0.75rem;
                font-weight: 700;
                color: #6f42c1;
                text-transform: uppercase;
                margin-bottom: 8px;
                display: block;
            }
            .form-control, .form-select {
                border-radius: 10px;
                background: #f8f9fa;
                border: 1px solid #e9ecef;
                padding: 10px 15px;
            }
            .input-group-text {
                border-radius: 10px 0 0 10px;
                background: #f8f9fa;
                border: 1px solid #e9ecef;
                color: #6f42c1;
            }
            .input-group > .form-control, .input-group > .form-select {
                border-radius: 0 10px 10px 0;
            }
            .back-link {
                text-decoration: none;
                color: rgba(255,255,255,0.8);
                font-size: 0.9rem;
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
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8">
                    <a href="http://localhost/gym_class/View/trainer_manage_schedule.php" class="back-link">
                        <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                    </a>

                    <div class="card shadow">
                        <div class="header-purple">
                            <i class="bi bi-calendar-plus-fill fs-2"></i>
                            <h2 class="h4 mb-0 fw-bold">ASSIGN NEW GYM CLASS</h2>
                        </div>

                        <div class="card-body p-4 p-md-5">
                            <form action="" method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Assigned Trainer</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person-check-fill"></i></span>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" readonly>
                                        <input type="hidden" name="trainer_id" value="<?php echo $_SESSION['user_id']; ?>">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Select Class Type</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-activity"></i></span>
                                        <select name="class_name" class="form-select" required>
                                            <option value="" disabled selected>-- Choose a Class --</option>
                                            <option value="HIIT">HIIT</option>
                                            <option value="Dance">Dance</option>
                                            <option value="Zumba">Zumba</option>
                                            <option value="Yoga">Yoga</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Class Date</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                                        <input type="date" name="class_date" class="form-control" required>
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Start Time</label>
                                        <input type="time" name="start_time" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">End Time</label>
                                        <input type="time" name="end_time" class="form-control" required>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Max Capacity</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-people"></i></span>
                                        <input type="number" name="max_capacity" class="form-control" value="20" min="1" required>
                                    </div>
                                </div>

                                <button type="submit" name="submit_schedule" class="btn btn-purple w-100">
                                    <i class="bi bi-check-circle me-2"></i>CONFIRM SCHEDULE
                                </button>
                            </form>

                            <div class="mt-4">
                                <?php
                                if (isset($_POST['submit_schedule'])) {
                                    $url = "http://localhost/gym_class/Services/Schedule_service.php";
                                    $ch = curl_init($url);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($_POST));
                                    $response = curl_exec($ch);

                                    if (curl_errno($ch)) {
                                        echo "<div class='alert alert-danger small'>Connection Error: " . curl_error($ch) . "</div>";
                                    } else {
                                        $result = json_decode($response);
                                        if (isset($result->status) && $result->status == "success") {
                                            echo "<div class='alert alert-success small'><i class='bi bi-check-lg me-2'></i>" . $result->message . "</div>";
                                        } else {
                                            $msg = $result->message ?? "An unexpected error occurred.";
                                            echo "<div class='alert alert-danger small'><i class='bi bi-exclamation-triangle me-2'></i>" . $msg . "</div>";
                                        }
                                    }
                                    curl_close($ch);
                                }
                                ?>
                            </div>
                        </div> 
                    </div> 
                </div> 
            </div> 
        </div> 

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>