<?php
require_once '../config/db.php';
session_start();

$user = new User();

if (!$user->isLoggedIn() || !$user->isAdmin()) {
    header("Location: ../login.php");
    exit();
}

$users = $user->getAllUsers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - User Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="page-wrapper">
        <nav class="navbar">
            <div class="container">
                <a href="dashboard.php" class="navbar-brand">
                    <i class="fas fa-users-cog"></i> Admin Panel
                </a>
                <div class="navbar-nav">
                    <span class="nav-link">
                        <i class="fas fa-user-shield"></i> Welcome, Admin
                    </span>
                    <a href="../logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </nav>

        <div class="container">
            <div class="grid">
                <div class="dashboard-card">
                    <div class="card-icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Total Users</h3>
                    <p class="stats-number"><?php echo count($users); ?></p>
                </div>
                <div class="dashboard-card">
                    <div class="card-icon success">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h3>Admins</h3>
                    <p class="stats-number">
                        <?php 
                        $adminCount = array_filter($users, function($u) { 
                            return $u['role'] == 'admin'; 
                        });
                        echo count($adminCount);
                        ?>
                    </p>
                </div>
                <div class="dashboard-card">
                    <div class="card-icon primary">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Regular Users</h3>
                    <p class="stats-number">
                        <?php 
                        $userCount = array_filter($users, function($u) { 
                            return $u['role'] == 'user'; 
                        });
                        echo count($userCount);
                        ?>
                    </p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-users"></i> User Management</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_GET['msg'])): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?php 
                            if ($_GET['msg'] == 'user_deleted') echo "User deleted successfully!";
                            if ($_GET['msg'] == 'user_updated') echo "User updated successfully!";
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php 
                            if ($_GET['error'] == 'cannot_delete_self') echo "You cannot delete your own account!";
                            if ($_GET['error'] == 'delete_failed') echo "Failed to delete user!";
                            ?>
                        </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Profile</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($users as $userData): ?>
                                <tr>
                                    <td data-label="ID">#<?php echo $userData['id']; ?></td>
                                    <td data-label="Profile">
                                        <img src="../uploads/<?php echo $userData['profile_photo']; ?>" 
                                             alt="Profile" class="profile-img">
                                    </td>
                                    <td data-label="Username">
                                        <?php echo htmlspecialchars($userData['username']); ?>
                                    </td>
                                    <td data-label="Email">
                                        <?php echo htmlspecialchars($userData['email']); ?>
                                    </td>
                                    <td data-label="Role">
                                        <span class="badge bg-<?php echo $userData['role'] == 'admin' ? 'danger' : 'primary'; ?>">
                                            <?php echo $userData['role']; ?>
                                        </span>
                                    </td>
                                    <td data-label="Joined">
                                        <?php echo date('M d, Y', strtotime($userData['created_at'])); ?>
                                    </td>
                                    <td data-label="Actions">
                                        <a href="view_user.php?id=<?php echo $userData['id']; ?>" 
                                           class="btn btn-sm btn-info" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit_user.php?id=<?php echo $userData['id']; ?>" 
                                           class="btn btn-sm btn-warning" title="Edit User">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if($userData['id'] != $_SESSION['user_id']): ?>
                                        <a href="delete_user.php?id=<?php echo $userData['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this user?')"
                                           title="Delete User">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <footer>
            <div class="container">
                <p class="text-center">&copy; 2026 Russell Evan Loquinario User Management System. All rights reserved.</p>
            </div>
        </footer>
    </div>
</body>
</html>