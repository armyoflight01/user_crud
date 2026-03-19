<?php
require_once '../config/db.php';
session_start();

$user = new User();

if (!$user->isLoggedIn() || !$user->isAdmin()) {
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'] ?? 0;
$error = '';
$success = '';

$userData = $user->getUserById($id);

if (!$userData) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $newPassword = $_POST['new_password'] ?? '';
    
    $profilePhoto = $userData['profile_photo'];
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
        $uploadedFile = $user->uploadProfilePhoto($_FILES['profile_photo']);
        if ($uploadedFile) {
            if ($profilePhoto != 'default.jpg' && file_exists('../uploads/' . $profilePhoto)) {
                unlink('../uploads/' . $profilePhoto);
            }
            $profilePhoto = $uploadedFile;
        }
    }
    
    if ($user->updateUser($id, $username, $email, $role, $profilePhoto, $newPassword)) {
        $success = "User updated successfully!";
        $userData = $user->getUserById($id);
    } else {
        $error = "Failed to update user";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Admin Panel</title>
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
                    <a href="dashboard.php" class="nav-link">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
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
                            <h4><i class="fas fa-edit"></i> Edit User: <?php echo htmlspecialchars($userData['username']); ?></h4>
                        </div>
                        <div class="card-body">
                            <?php if($error): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <?php echo $error; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if($success): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i>
                                    <?php echo $success; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="" enctype="multipart/form-data" id="editForm">
                                <div class="grid" style="grid-template-columns: 1fr 1fr;">
                                    <div>
                                        <div class="form-group">
                                            <label for="username" class="form-label">
                                                <i class="fas fa-user"></i> Username
                                            </label>
                                            <input type="text" class="form-control" id="username" name="username" 
                                                   value="<?php echo htmlspecialchars($userData['username']); ?>" required>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="form-group">
                                            <label for="email" class="form-label">
                                                <i class="fas fa-envelope"></i> Email
                                            </label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?php echo htmlspecialchars($userData['email']); ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="role" class="form-label">
                                        <i class="fas fa-user-tag"></i> Role
                                    </label>
                                    <select class="form-control" id="role" name="role">
                                        <option value="user" <?php echo $userData['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                                        <option value="admin" <?php echo $userData['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="new_password" class="form-label">
                                        <i class="fas fa-key"></i> New Password (leave empty to keep current)
                                    </label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" 
                                           placeholder="Enter new password">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Current Profile Photo</label>
                                    <div class="text-center">
                                        <img src="../uploads/<?php echo $userData['profile_photo']; ?>" 
                                             alt="Profile" class="profile-preview" id="currentPhoto">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="profile_photo" class="form-label">
                                        <i class="fas fa-camera"></i> New Profile Photo (optional)
                                    </label>
                                    <div class="custom-file-upload" id="uploadArea">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <p>Click to upload new photo</p>
                                        <input type="file" id="profile_photo" name="profile_photo" accept="image/*">
                                    </div>
                                    <div id="newImagePreview" class="text-center mt-3" style="display: none;">
                                        <img src="#" alt="New Preview" class="profile-preview">
                                        <button type="button" class="btn btn-sm btn-danger mt-2" id="removeImage">
                                            <i class="fas fa-times"></i> Remove
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group text-center">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update User
                                    </button>
                                    <a href="dashboard.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
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
                <p class="text-center">&copy; 2024 User Management System. All rights reserved.</p>
            </div>
        </footer>
    </div>

    <script>
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('profile_photo');
        const newImagePreview = document.getElementById('newImagePreview');
        const previewImg = newImagePreview.querySelector('img');
        const removeBtn = document.getElementById('removeImage');
        const currentPhoto = document.getElementById('currentPhoto');

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
                    newImagePreview.style.display = 'block';
                    uploadArea.style.display = 'none';
                    currentPhoto.style.opacity = '0.5';
                }
                reader.readAsDataURL(file);
            }
        });

        removeBtn.addEventListener('click', function() {
            fileInput.value = '';
            newImagePreview.style.display = 'none';
            uploadArea.style.display = 'block';
            currentPhoto.style.opacity = '1';
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