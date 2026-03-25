<?php
// logout.php

session_start(); // Start the session to access it

session_unset(); // Unset all session variables

session_destroy(); // Destroy the session data on the server

// Redirect the user back to the main page
header("Location: login.php");
exit();
?>