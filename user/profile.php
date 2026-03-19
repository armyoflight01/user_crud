<?php
require_once '../config/db.php';
session_start();

$user = new User();

if (!$user->isLoggedIn()) {
    header("Location: ../login.php");
    exit();
}

$error = '';
$success = '';
$userData = $user->getUserById($_SESSION['user_id']);

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $profile_photo = $userData['profile_photo'];
    if(isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
        $uploadedFile = $user->uploadProfilePhoto($_FILES['profile_photo']);
        if($uploadedFile) {
            if($profile_photo != 'default.jpg' && file_exists('../uploads/' . $profile_photo)) {
                unlink('../uploads/' . $profile_photo);
            }
            $profile_photo = $uploadedFile;
        }
    }
    
    if($user->updateUser($_SESSION['user_id'], $username, $email, $userData['role'], $profile_photo)) {
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $success = "Profile updated successfully!";
        
        if(!empty($new_password)) {
            if(password_verify($current_password, $userData['password'])) {
                if($new_password == $confirm_password && strlen($new_password) >= 6) {
                    $user->updateUser($_SESSION['user_id'], $username, $email, $userData['role'], $profile_photo, $new_password);
                    $success .= " Password changed successfully!";
                } else {
                    $error = "New passwords do not match or are too short";
                }
            } else {
                $error = "Current password is incorrect";
            }
        }
        
        $userData = $user->getUserById($_SESSION['user_id']);
    } else {
        $error = "Failed to update profile";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - User Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="page-wrapper">
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

        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-edit"></i> Edit Profile</h4>
                        </div>
                        <div class="card-body">
                            <?php if($error): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>
                            <?php if($success): ?>
                                <div class="alert alert-success"><?php echo $success; ?></div>
                            <?php endif; ?>
                            
                            <form method="POST" action="" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="username" class="form-label">Username</label>
                                            <input type="text" class="form-control" id="username" name="username" 
                                                   value="<?php echo htmlspecialchars($userData['username']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?php echo htmlspecialchars($userData['email']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <img src="../uploads/<?php echo $userData['profile_photo']; ?>" 
                                             alt="Profile" class="profile-avatar" style="margin-bottom: 10px;">
                                        <div class="form-group">
                                            <label for="profile_photo" class="form-label">Change Photo</label>
                                            <input type="file" class="form-control" id="profile_photo" name="profile_photo" accept="image/*">
                                        </div>
                                    </div>
                                </div>
                                
                                <hr>
                                <h5>Change Password (optional)</h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="current_password" class="form-label">Current Password</label>
                                            <input type="password" class="form-control" id="current_password" name="current_password">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="new_password" class="form-label">New Password</label>
                                            <input type="password" class="form-control" id="new_password" name="new_password">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group text-center">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Profile
                                    </button>
                                    <a href="dashboard.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                                    </a>
                                </div>
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