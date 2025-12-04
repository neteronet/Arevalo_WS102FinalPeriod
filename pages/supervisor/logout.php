<?php
session_start();

// Destroy the session
session_unset();
session_destroy();

// Redirect to supervisor login page
header("Location: login.php");
exit;
?>


