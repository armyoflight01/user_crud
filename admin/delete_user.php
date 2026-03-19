<?php
require_once '../config/db.php';
session_start();

$user = new User();

if (!$user->isLoggedIn() || !$user->isAdmin()) {
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'] ?? 0;

if ($id == $_SESSION['user_id']) {
    header("Location: dashboard.php?error=cannot_delete_self");
    exit();
}

if ($user->deleteUser($id)) {
    header("Location: dashboard.php?msg=user_deleted");
} else {
    header("Location: dashboard.php?error=delete_failed");
}
exit();
?>