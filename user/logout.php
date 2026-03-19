<?php
require_once 'config/db.php';  // Adjust path based on file location
session_start();

$user = new User();
$user->logout();

header("Location: login.php");
exit();
?>