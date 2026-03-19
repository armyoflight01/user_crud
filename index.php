<?php
require_once 'config/db.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="page-wrapper">
        <nav class="navbar">
            <div class="container">
                <a href="index.php" class="navbar-brand">
                    <i class="fas fa-users-cog"></i> User Management System
                </a>
                <div class="navbar-nav">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <?php if($_SESSION['role'] == 'admin'): ?>
                            <a href="admin/dashboard.php" class="nav-link">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        <?php else: ?>
                            <a href="user/dashboard.php" class="nav-link">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        <?php endif; ?>
                        <a href="logout.php" class="nav-link">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="nav-link">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <a href="register.php" class="nav-link">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>

        <div class="container">
            <div class="jumbotron">
                <h1><i class="fas fa-users-cog"></i> User Management System</h1>
                <p>A complete OOP PHP user management system with admin panel and role-based access control.</p>
                <?php if(!isset($_SESSION['user_id'])): ?>
                    <div class="mt-4">
                        <a href="register.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-user-plus"></i> Get Started
                        </a>
                        <a href="login.php" class="btn btn-success btn-lg">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="grid">
                <div class="dashboard-card text-center">
                    <div class="card-icon primary">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h3>Admin Panel</h3>
                    <p>Complete admin dashboard to manage all users with full CRUD operations.</p>
                </div>
                <div class="dashboard-card text-center">
                    <div class="card-icon success">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>User Management</h3>
                    <p>Create, read, update, and delete users with profile photo upload.</p>
                </div>
                <div class="dashboard-card text-center">
                    <div class="card-icon primary">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h3>Secure Authentication</h3>
                    <p>Password hashing, session management, and role-based access control.</p>
                </div>
                <div class="dashboard-card text-center">
                    <div class="card-icon success">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Responsive Design</h3>
                    <p>Beautiful and responsive UI that works on all devices.</p>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h4><i class="fas fa-info-circle"></i> Demo Credentials</h4>
                </div>
                <div class="card-body">
                    <div class="grid" style="grid-template-columns: repeat(2, 1fr);">
                        <div>
                            <h5><i class="fas fa-user-shield"></i> Admin Account</h5>
                            <p><strong>Email:</strong> armyoflight01@gmail.com</p>
                            <p><strong>Password:</strong> 7895462130</p>
                            <p><strong>Role:</strong> Admin</p>
                        </div>
                        <div>
                            <h5><i class="fas fa-user"></i> User Account</h5>
                            <p><strong>Email:</strong> (Register as user)</p>
                            <p><strong>Password:</strong> (Your chosen password)</p>
                            <p><strong>Role:</strong> User</p>
                            <p><small>Upload profile photo during registration</small></p>
                        </div>
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