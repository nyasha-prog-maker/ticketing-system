<?php
// actions/logout.php
session_start();

// Unset all session variables
$_SESSION = array();

// Completely obliterate the session file on the server
session_destroy();

// Redirect clean back to the login screen
header("Location: ../index.php");
exit;
