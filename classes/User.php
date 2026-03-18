<?php
require_once __DIR__ . '/../config/Database.php';

class User {
    private $pdo;
    const ADMIN_EMAIL = 'armyoflight01@gmail.com';
    const ADMIN_PASSWORD = '7895462130';

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    // User registration with photo upload
    public function register($username, $email, $password, $profilePhoto = 'default.jpg') {
        try {
            // Check if user already exists
            if ($this->emailExists($email)) {
                return ['success' => false, 'message' => 'Email already exists'];
            }

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users (username, email, password, role, profile_photo, created_at) 
                    VALUES (:username, :email, :password, 'user', :profile_photo, NOW())";
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $hashedPassword,
                ':profile_photo' => $profilePhoto
            ]);

            if ($result) {
                return ['success' => true, 'message' => 'Registration successful!'];
            }
            
            return ['success' => false, 'message' => 'Registration failed'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    // Login (for both admin and users)
    public function login($email, $password, $selectedRole) {
        try {
            // Special case for admin
            if ($email === self::ADMIN_EMAIL && $password === self::ADMIN_PASSWORD) {
                if ($selectedRole === 'admin') {
                    // Check if admin exists in database
                    $sql = "SELECT * FROM users WHERE email = :email";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([':email' => $email]);
                    $user = $stmt->fetch();

                    if (!$user) {
                        // Create admin if not exists
                        $hashedPassword = password_hash(self::ADMIN_PASSWORD, PASSWORD_DEFAULT);
                        $sql = "INSERT INTO users (username, email, password, role, profile_photo, created_at) 
                                VALUES (:username, :email, :password, 'admin', :profile_photo, NOW())";
                        $stmt = $this->pdo->prepare($sql);
                        $stmt->execute([
                            ':username' => 'Admin',
                            ':email' => self::ADMIN_EMAIL,
                            ':password' => $hashedPassword,
                            ':profile_photo' => 'default.jpg'
                        ]);
                        
                        $userId = $this->pdo->lastInsertId();
                    } else {
                        $userId = $user['id'];
                    }

                    $_SESSION['user_id'] = $userId;
                    $_SESSION['role'] = 'admin';
                    $_SESSION['email'] = self::ADMIN_EMAIL;
                    
                    return ['success' => true, 'role' => 'admin'];
                } else {
                    return ['success' => false, 'message' => 'Invalid role selection for admin'];
                }
            }

            // Regular user login
            $sql = "SELECT * FROM users WHERE email = :email AND role = 'user'";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];
                
                return ['success' => true, 'role' => 'user'];
            }

            return ['success' => false, 'message' => 'Invalid email or password'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    // Get all users (for admin)
    public function getAllUsers() {
        try {
            $sql = "SELECT * FROM users ORDER BY created_at DESC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    // Get user by ID
    public function getUserById($id) {
        try {
            $sql = "SELECT * FROM users WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }

    // Update user (admin function) with photo upload
    public function updateUser($id, $username, $email, $role, $profilePhoto = null, $newPassword = null) {
        try {
            $sql = "UPDATE users SET username = :username, email = :email, role = :role";
            $params = [
                ':username' => $username,
                ':email' => $email,
                ':role' => $role,
                ':id' => $id
            ];

            if ($profilePhoto !== null) {
                $sql .= ", profile_photo = :profile_photo";
                $params[':profile_photo'] = $profilePhoto;
            }

            if ($newPassword !== null && !empty($newPassword)) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $sql .= ", password = :password";
                $params[':password'] = $hashedPassword;
            }

            $sql .= " WHERE id = :id";
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
            
        } catch (PDOException $e) {
            return false;
        }
    }

    // Delete user (admin function)
    public function deleteUser($id) {
        try {
            // Get user data to delete profile photo
            $user = $this->getUserById($id);
            
            if ($user && $user['profile_photo'] != 'default.jpg') {
                $filePath = __DIR__ . '/../uploads/' . $user['profile_photo'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            $sql = "DELETE FROM users WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':id' => $id]);
            
        } catch (PDOException $e) {
            return false;
        }
    }

    // Upload profile photo
    public function uploadProfilePhoto($file) {
        if ($file['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $file['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                // Generate unique filename
                $newFilename = time() . '_' . uniqid() . '.' . $ext;
                $uploadPath = __DIR__ . '/../uploads/' . $newFilename;
                
                // Create uploads directory if it doesn't exist
                if (!is_dir(__DIR__ . '/../uploads')) {
                    mkdir(__DIR__ . '/../uploads', 0777, true);
                }
                
                if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                    return $newFilename;
                }
            }
        }
        return null;
    }

    // Check if email exists
    private function emailExists($email) {
        $sql = "SELECT id FROM users WHERE email = :email";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch() ? true : false;
    }

    // Logout
    public function logout() {
        session_destroy();
        return true;
    }

    // Check if logged in
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    // Check if admin
    public function isAdmin() {
        return (isset($_SESSION['role']) && $_SESSION['role'] == 'admin');
    }

    // Get current user ID
    public function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }
}
?>