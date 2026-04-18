<?php
require_once('../Services/UserController.php');

// Security Check: Only Admins should see this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: profile.php");
    exit;
}

// Fetching data from the service
function fetchUsersFromService() {
    $url = 'http://localhost/gym_class/Model/user_service.php';
    $response = @file_get_contents($url);
    return ($response) ? json_decode($response, true) : [];
}

$users = fetchUsersFromService();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>User Management - Anytime Fitness</title>
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
            .user-row:hover {
                background-color: #f1f0ff;
                transition: 0.3s;
            }
            /* Role Badges */
            .badge-admin {
                background-color: #6f42c1;
                color: white;
            }
            .badge-trainer {
                background-color: #0d6efd;
                color: white;
            }
            .badge-member {
                background-color: #6c757d;
                color: white;
            }

            .btn-back {
                color: white;
                text-decoration: none;
                font-size: 0.9rem;
                transition: 0.3s;
            }
            .btn-back:hover {
                color: #d1d1d1;
            }
        </style>
    </head>
    <body>

        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">

                    <div class="mb-3">
                        <a href="profile.php" class="btn-back">
                            <i class="bi bi-arrow-left-circle me-1"></i> Back to Dashboard
                        </a>
                    </div>

                    <div class="card management-card">
                        <div class="header-section">
                            <i class="bi bi-people-fill fs-1 mb-2"></i>
                            <h1 class="h3 fw-bold mb-0">USER MANAGEMENT</h1>
                            <p class="mb-0 small opacity-75">View and manage all registered accounts</p>
                        </div>

                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th class="ps-4">ID</th>
                                            <th>Full Name</th>
                                            <th>Email Address</th>
                                            <th>Role</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($users)): ?>
                                            <?php foreach ($users as $user): ?> 
                                                <tr class="user-row">
                                                    <td class="ps-4 fw-bold">#<?php echo $user['id']; ?></td>
                                                    <td>
                                                        <div class="fw-bold"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                    <td>
                                                        <?php
                                                        $roleClass = 'badge-member';
                                                        if ($user['role'] == 'Admin')
                                                            $roleClass = 'badge-admin';
                                                        if ($user['role'] == 'Trainer')
                                                            $roleClass = 'badge-trainer';
                                                        ?>
                                                        <span class="badge <?php echo $roleClass; ?> rounded-pill px-3">
                                                            <?php echo $user['role']; ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="edit_role.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                                            <i class="bi bi-pencil-square"></i> Edit Role
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-5 text-muted">
                                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                                    No users found in the system.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card-footer bg-light p-3 text-center">
                            <small class="text-muted">Total Registered Users: <?php echo count($users); ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>