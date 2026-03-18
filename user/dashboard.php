<?php
require_once '../classes/User.php';
session_start();

$user = new User();

// Check if user is logged in
if (!$user->isLoggedIn()) {
    header("Location: ../login.php");
    exit();
}

// Get current user data
$userData = $user->getUserById($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - User Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="page-wrapper">
        <!-- Navbar -->
        <nav class="navbar">
            <div class="container">
                <a href="dashboard.php" class="navbar-brand">
                    <i class="fas fa-users-cog"></i> User Dashboard
                </a>
                <div class="navbar-nav">
                    <span class="nav-link">
                        <i class="fas fa-user"></i> Welcome, <?php echo htmlspecialchars($userData['username']); ?>
                    </span>
                    <a href="../logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-user-circle"></i> Profile</h4>
                        </div>
                        <div class="card-body text-center">
                            <img src="../uploads/<?php echo $userData['profile_photo']; ?>" 
                                 alt="Profile" class="profile-avatar">
                            <h3 class="mt-3"><?php echo htmlspecialchars($userData['username']); ?></h3>
                            <span class="badge bg-primary"><?php echo $userData['role']; ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-info-circle"></i> Account Information</h4>
                        </div>
                        <div class="card-body">
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-id-card"></i> User ID:</span>
                                <span class="info-value">#<?php echo $userData['id']; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-envelope"></i> Email:</span>
                                <span class="info-value"><?php echo htmlspecialchars($userData['email']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-calendar"></i> Member Since:</span>
                                <span class="info-value"><?php echo date('F d, Y', strtotime($userData['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header">
                            <h4><i class="fas fa-shield-alt"></i> Account Status</h4>
                        </div>
                        <div class="card-body">
                            <div class="grid" style="grid-template-columns: repeat(2, 1fr);">
                                <div class="dashboard-card">
                                    <div class="card-icon success">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <h4>Account Status</h4>
                                    <span class="status-badge active">Active</span>
                                </div>
                                <div class="dashboard-card">
                                    <div class="card-icon primary">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <h4>Account Age</h4>
                                    <p><?php 
                                        $created = new DateTime($userData['created_at']);
                                        $now = new DateTime();
                                        $diff = $created->diff($now);
                                        echo $diff->days . ' days';
                                    ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer>
            <div class="container">
                <p class="text-center">&copy; 2026 Russell Evan Loquinario User Management System. All rights reserved.</p>
            </div>
        </footer>
    </div>
</body>
</html>