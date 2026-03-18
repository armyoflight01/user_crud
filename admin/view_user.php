<?php
require_once '../classes/User.php';
session_start();

$user = new User();

// Check if user is logged in and is admin
if (!$user->isLoggedIn() || !$user->isAdmin()) {
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'] ?? 0;
$userData = $user->getUserById($id);

if (!$userData) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View User - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="page-wrapper">
        <!-- Navbar -->
        <nav class="navbar">
            <div class="container">
                <a href="dashboard.php" class="navbar-brand">
                    <i class="fas fa-users-cog"></i> Admin Panel
                </a>
                <div class="navbar-nav">
                    <a href="dashboard.php" class="nav-link">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <a href="../logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="container">
            <div class="profile-container">
                <div class="card">
                    <div class="profile-header">
                        <img src="../uploads/<?php echo $userData['profile_photo']; ?>" 
                             alt="Profile" class="profile-avatar">
                        <h2><?php echo htmlspecialchars($userData['username']); ?></h2>
                        <span class="badge bg-<?php echo $userData['role'] == 'admin' ? 'danger' : 'primary'; ?>">
                            <?php echo $userData['role']; ?>
                        </span>
                    </div>
                    <div class="profile-info">
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
                    <div class="card-footer text-center">
                        <a href="edit_user.php?id=<?php echo $userData['id']; ?>" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit User
                        </a>
                        <?php if($userData['id'] != $_SESSION['user_id']): ?>
                        <a href="delete_user.php?id=<?php echo $userData['id']; ?>" 
                           class="btn btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this user?')">
                            <i class="fas fa-trash"></i> Delete User
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer>
            <div class="container">
                <p class="text-center">&copy; 2024 User Management System. All rights reserved.</p>
            </div>
        </footer>
    </div>
</body>
</html>