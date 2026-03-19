<?php
require_once 'config/db.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['role'] == 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php'));
    exit();
}

$user = new User();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = "All fields are required";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        $profilePhoto = 'default.jpg';
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
            $uploadedFile = $user->uploadProfilePhoto($_FILES['profile_photo']);
            if ($uploadedFile) {
                $profilePhoto = $uploadedFile;
            }
        }
        
        $result = $user->register($username, $email, $password, $profilePhoto);
        if ($result['success']) {
            $success = $result['message'];
            header("refresh:2;url=login.php");
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
    <title>Register - User Management System</title>
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
                    <a href="login.php" class="nav-link">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="register.php" class="nav-link active">
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
                            <i class="fas fa-user-plus fa-3x"></i>
                            <h4>Create User Account</h4>
                            <p>Register as a regular user</p>
                        </div>
                        <div class="card-body">
                            <?php if ($error): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <?php echo htmlspecialchars($error); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($success): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i>
                                    <?php echo htmlspecialchars($success); ?>
                                    <br>Redirecting to login...
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="" enctype="multipart/form-data" id="registerForm">
                                <div class="form-group">
                                    <label for="username" class="form-label">
                                        <i class="fas fa-user"></i> Username
                                    </label>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                                           required placeholder="Enter your username">
                                </div>

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
                                           required placeholder="Enter your password (min. 6 characters)">
                                </div>

                                <div class="form-group">
                                    <label for="confirm_password" class="form-label">
                                        <i class="fas fa-lock"></i> Confirm Password
                                    </label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                           required placeholder="Confirm your password">
                                </div>

                                <div class="form-group">
                                    <label for="profile_photo" class="form-label">
                                        <i class="fas fa-camera"></i> Profile Photo (optional)
                                    </label>
                                    <div class="custom-file-upload" id="uploadArea">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <p>Click to upload your profile photo</p>
                                        <input type="file" id="profile_photo" name="profile_photo" accept="image/*">
                                    </div>
                                    <div id="imagePreview" class="text-center mt-3" style="display: none;">
                                        <img src="#" alt="Preview" class="profile-preview">
                                        <button type="button" class="btn btn-sm btn-danger mt-2" id="removeImage">
                                            <i class="fas fa-times"></i> Remove
                                        </button>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-user-plus"></i> Register
                                </button>

                                <div class="divider">OR</div>

                                <p class="text-center">
                                    Already have an account? 
                                    <a href="login.php" class="btn-link">Login here</a>
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

    <script>
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('profile_photo');
        const imagePreview = document.getElementById('imagePreview');
        const previewImg = imagePreview.querySelector('img');
        const removeBtn = document.getElementById('removeImage');

        uploadArea.addEventListener('click', function() {
            fileInput.click();
        });

        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) {
                    alert('File is too large. Maximum size is 5MB.');
                    fileInput.value = '';
                    return;
                }

                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!validTypes.includes(file.type)) {
                    alert('Please upload a valid image file (JPG, JPEG, PNG, GIF)');
                    fileInput.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    imagePreview.style.display = 'block';
                    uploadArea.style.display = 'none';
                }
                reader.readAsDataURL(file);
            }
        });

        removeBtn.addEventListener('click', function() {
            fileInput.value = '';
            imagePreview.style.display = 'none';
            uploadArea.style.display = 'block';
            previewImg.src = '#';
        });

        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = 'var(--success-color)';
            this.style.backgroundColor = 'rgba(76, 201, 240, 0.1)';
        });

        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.borderColor = 'var(--primary-color)';
            this.style.backgroundColor = 'transparent';
        });

        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.borderColor = 'var(--primary-color)';
            this.style.backgroundColor = 'transparent';
            
            const file = e.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                fileInput.files = e.dataTransfer.files;
                const event = new Event('change', { bubbles: true });
                fileInput.dispatchEvent(event);
            } else {
                alert('Please drop an image file');
            }
        });
    </script>
</body>
</html>