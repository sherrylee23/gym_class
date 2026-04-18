<?php
require_once('../Services/UserController.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token if it does not exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $controller = new UserController();
    // This calls the registerUser method added to UserController.php
    $error_message = $controller->registerUser($_POST);
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Register - Anytime Fitness</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

        <style>
            body {
                font-family: 'Poppins', sans-serif;
                background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
                height: 100vh;
                display: flex;
                align-items: center;
            }
            .register-card {
                background: white;
                border-radius: 20px;
                box-shadow: 0 15px 35px rgba(0,0,0,0.2);
                overflow: hidden;
                border: none;
            }
            .logo-section {
                background-color: #6f42c1;
                padding: 30px;
                text-align: center;
                color: white;
            }
            .logo-text {
                font-size: 2rem;
                font-weight: 800;
                letter-spacing: 1px;
                text-transform: uppercase;
                margin: 0;
            }
            .btn-purple {
                background-color: #6f42c1;
                color: white;
                border: none;
                transition: 0.3s;
            }
            .btn-purple:hover {
                background-color: #59359a;
                color: white;
                transform: translateY(-2px);
            }
            .form-control:focus {
                border-color: #6f42c1;
                box-shadow: 0 0 0 0.25rem rgba(111, 66, 193, 0.25);
            }
            .purple-text {
                color: #6f42c1;
            }
        </style>
    </head>
    <body>

        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="card register-card">

                        <div class="logo-section">
                            <i class="bi bi-lightning-charge-fill fs-1"></i>
                            <h1 class="logo-text">Anytime Fitness</h1>
                        </div>

                        <div class="p-4 p-md-5">
                            <div class="text-center mb-4">
                                <h2 class="fw-bold purple-text">Join Us</h2>
                                <p class="text-muted small">Enter your info to keep it going!</p>
                            </div>

                            <?php if (!empty($error_message)): ?>
                                <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                                    <?php echo htmlspecialchars($error_message); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                                <div class="mb-3">
                                    <label class="form-label small fw-bold">FULL NAME</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                        <input type="text" name="full_name" class="form-control bg-light border-start-0" placeholder="Name" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold">EMAIL ADDRESS</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                                        <input type="email" name="email" class="form-control bg-light border-start-0" placeholder="xxx@gmail.com" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="profile-label">Phone Number (11 Digits)</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-phone small"></i></span>
                                        <input 
                                            type="text" 
                                            name="phone_number" 
                                            class="form-control bg-light border-start-0" 
                                            placeholder="01234567890" 
                                            required
                                            maxlength="11"
                                            minlength="11"
                                            pattern="\d{11}"
                                            title="Please enter exactly 11 numbers without dash"
                                        >
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label small fw-bold">SECURE PASSWORD</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield-lock text-muted"></i></span>
                                        <input type="password" name="password" class="form-control bg-light border-start-0" placeholder="••••••••" required>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-purple w-100 py-2 fw-bold mb-3">CREATE ACCOUNT</button>
                            </form>

                            <div class="text-center">
                                <p class="small text-muted mb-0">Already a member? <a href="login.php" class="purple-text fw-bold text-decoration-none">Login here</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>