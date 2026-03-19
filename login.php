<?php
require_once 'config/db.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['role'] == 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php'));
    exit();
}

$user = new User();
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (empty($email) || empty($password)) {
        $error = "Email and password are required";
    } else {
        $result = $user->login($email, $password, $role);
        
        if ($result['success']) {
            if ($result['role'] == 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: user/dashboard.php");
            }
            exit();
        } else {
            $error = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - User Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="page-wrapper">
        <nav class="navbar">
            <div class="container">
                <a href="index.php" class="navbar-brand">
                    <i class="fas fa-users-cog"></i> Loquinario User Management System
                </a>
                <div class="navbar-nav">
                    <a href="login.php" class="nav-link active">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="register.php" class="nav-link">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                </div>
            </div>
        </nav>

        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card auth-card">
                        <div class="card-header">
                            <i class="fas fa-sign-in-alt fa-3x"></i>
                            <h4>Welcome Back</h4>
                            <p>Please login to your account</p>
                        </div>
                        <div class="card-body">
                            <?php if ($error): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <?php echo htmlspecialchars($error); ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="">
                                <div class="form-group">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope"></i> Email
                                    </label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                           required placeholder="Enter your email">
                                </div>

                                <div class="form-group">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock"></i> Password
                                    </label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           required placeholder="Enter your password">
                                </div>

                                <div class="form-group">
                                    <label for="role" class="form-label">
                                        <i class="fas fa-user-tag"></i> Login as
                                    </label>
                                    <select class="form-control" id="role" name="role" required>
                                        <option value="user">User</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-sign-in-alt"></i> Login
                                </button>

                                <div class="divider">OR</div>

                                <p class="text-center">
                                    Don't have an account? 
                                    <a href="register.php" class="btn-link">Register here</a>
                                </p>
                            </form>
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