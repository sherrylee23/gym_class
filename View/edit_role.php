<?php
require_once('../Services/UserController.php');
require_once('../Model/UserModel.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: profile.php");
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$model = new UserModel();
$userToEdit = null;
$error_message = "";

if (isset($_GET['id'])) {
    $stmt = getDBConnection()->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $userToEdit = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userToEdit) {
        header("Location: DisplayUsers.php");
        exit;
    }
} else {
    header("Location: DisplayUsers.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ctrl = new UserController();
    $error_message = $ctrl->changeUserRole($_POST);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User Role - Anytime Fitness</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        body {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Poppins', sans-serif;
        }
        .card {
            border-radius: 20px;
            border: none;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header-purple {
            background: #6f42c1;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .user-badge {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 25px;
            border-left: 4px solid #6f42c1;
        }
        .btn-purple {
            background: #6f42c1;
            color: white;
            padding: 12px;
            transition: 0.3s;
        }
        .btn-purple:hover {
            background: #59359a;
            color: white;
            transform: translateY(-2px);
        }
        .form-select:focus {
            border-color: #6f42c1;
            box-shadow: 0 0 0 0.25rem rgba(111, 66, 193, 0.25);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">

            <div class="card">
                <div class="header-purple">
                    <i class="bi bi-shield-lock fs-1"></i>
                    <h4 class="fw-bold mb-0 mt-2">ROLE MANAGEMENT</h4>
                </div>

                <div class="card-body p-4">
                    <div class="user-badge">
                        <label class="text-muted small fw-bold text-uppercase">Editing User</label>
                        <h5 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($userToEdit['full_name']); ?></h5>
                        <p class="small text-muted mb-0"><?php echo htmlspecialchars($userToEdit['email']); ?></p>
                    </div>

                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger py-2 small mb-3">
                            <i class="bi bi-exclamation-circle me-2"></i> <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <input type="hidden" name="user_id" value="<?php echo (int)$userToEdit['id']; ?>">

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-purple">ASSIGN NEW ROLE</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="bi bi-person-badge"></i></span>
                                <select name="role" class="form-select bg-light border-0">
                                    <option value="Member" <?php if($userToEdit['role'] == 'Member') echo 'selected'; ?>>Member</option>
                                    <option value="Trainer" <?php if($userToEdit['role'] == 'Trainer') echo 'selected'; ?>>Trainer</option>
                                    <option value="Admin" <?php if($userToEdit['role'] == 'Admin') echo 'selected'; ?>>Admin</option>
                                </select>
                            </div>
                            <div class="form-text mt-2 small">
                                <i class="bi bi-info-circle me-1"></i> This grants immediate access permissions.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-purple w-100 fw-bold shadow-sm">
                            <i class="bi bi-check2-circle me-2"></i>CONFIRM CHANGE
                        </button>

                        <div class="text-center mt-3">
                            <a href="DisplayUsers.php" class="text-decoration-none text-muted small fw-bold">
                                <i class="bi bi-x-circle me-1"></i> CANCEL & GO BACK
                            </a>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>