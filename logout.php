<?php
require_once 'config/db.php';
session_start();

$user = new User();
$user->logout();

header("Location: login.php");
exit();
?>