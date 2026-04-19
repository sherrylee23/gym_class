<?php
// author: Cheok Jia Xuen

session_start();
session_unset(); // Remove all session variables
session_destroy(); // Destroy the session entirely
header("Location: login.php"); // Redirect back to login page
exit();
