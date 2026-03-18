<?php
require_once '../config/database.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$error = '';
$success = '';

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Handle profile photo upload
    $profile_photo = $user['profile_photo'];
    if(isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_photo']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if(in_array($ext, $allowed)) {
            // Delete old photo if not default
            if($profile_photo != 'default.jpg' && file_exists('../uploads/' . $profile_photo)) {
                unlink('../uploads/' . $profile_photo);
            }
            
            $profile_photo = time() . '_' . $filename;
            move_uploaded_file($_FILES['profile_photo']['tmp_name'], '../uploads/' . $profile_photo);
        }
    }
    
    // Update basic info
    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, profile_photo = ? WHERE id = ?");
    if($stmt->execute([$username, $email, $profile_photo, $_SESSION['user_id']])) {
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $success = "Profile updated successfully!";
        
        // Update password if provided
        if(!empty($new_password)) {
            if(password_verify($current_password, $user['password'])) {
                if($new_password == $confirm_password && strlen($new_password) >= 6) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashed_password, $_SESSION['user_id']]);
                    $success .= " Password changed successfully!";
                } else {
                    $error = "New passwords do not match or are too short";
                }
            } else {
                $error = "Current password is incorrect";
            }
        }
        
        // Refresh user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
    } else {
        $error = "Failed to update profile";
    }
}
?>

<?php include '../header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4>Edit Profile</h4>
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
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <img src="../uploads/<?php echo $user['profile_photo']; ?>" 
                                 alt="Profile" style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%; margin-bottom: 10px;">
                            <div class="mb-3">
                                <label for="profile_photo" class="form-label">Change Photo</label>
                                <input type="file" class="form-control" id="profile_photo" name="profile_photo" accept="image/*">
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    <h5>Change Password (optional)</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>