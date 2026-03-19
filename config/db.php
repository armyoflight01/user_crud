<?php
// config/db.php - Combined Database connection and User Class

class Database {
    private $host = 'localhost';
    private $dbname = 'user_crud';
    private $username = 'root';
    private $password = '';
    private $pdo;
    private static $instance = null;

    private function __construct() {
        try {
            $this->pdo = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname}",
                $this->username,
                $this->password
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }
}

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
            if ($this->emailExists($email)) {
                return ['success' => false, 'message' => 'Email already exists'];
            }

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
            if ($email === self::ADMIN_EMAIL && $password === self::ADMIN_PASSWORD) {
                if ($selectedRole === 'admin') {
                    $sql = "SELECT * FROM users WHERE email = :email";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([':email' => $email]);
                    $user = $stmt->fetch();

                    if (!$user) {
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

    public function getAllUsers() {
        try {
            $sql = "SELECT * FROM users ORDER BY created_at DESC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

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

    public function deleteUser($id) {
        try {
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

    public function uploadProfilePhoto($file) {
        if ($file['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $file['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $newFilename = time() . '_' . uniqid() . '.' . $ext;
                $uploadPath = __DIR__ . '/../uploads/' . $newFilename;
                
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

    private function emailExists($email) {
        $sql = "SELECT id FROM users WHERE email = :email";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch() ? true : false;
    }

    public function logout() {
        session_destroy();
        return true;
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function isAdmin() {
        return (isset($_SESSION['role']) && $_SESSION['role'] == 'admin');
    }

    public function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }
}
?>