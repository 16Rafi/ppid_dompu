<?php
require_once '../includes/config.php';

// Logout user
session_destroy();

// Redirect to login page
header('Location: index.php');
exit();
?>
