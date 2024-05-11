<?php
session_start();
session_destroy(); // Destroys all data associated with the current session
session_start(); // Starts a new session so you can set a success message
$_SESSION['success_message'] = 'You have been logged out';
header('Location: login.php'); // Redirects to the login page
exit;
?>