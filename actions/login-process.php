<?php
// actions/login-process.php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize inputs
    $username_input = trim($_POST['username']);
    $password_input = trim($_POST['password']);
    $role_input     = $_POST['role'];

    try {
        // Look up user by username OR email, ensuring their role matches their selection
        $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = :username OR email = :username) AND role = :role");
        $stmt->execute([
            'username' => $username_input,
            'role' => $role_input
        ]);
        
        $user = $stmt->fetch();

        // Verify user exists and check the hashed password match
        if ($user && password_verify($password_input, $user['password'])) {
            
            // Password is correct! Initialize global session variables
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['user_role'] = $user['role'];

            // Temporary success indicator (We will point this to a dashboard later)
            echo "<h1>Access Granted!</h1>";
            echo "<p>Welcome back, " . htmlspecialchars($user['username']) . ". You logged in successfully as an <strong>" . htmlspecialchars($user['role']) . "</strong>.</p>";
            echo "<p><a href='../index.php'>Log Out</a></p>";
            
        } else {
            // Authentication failed
            echo "<h1>Access Denied</h1>";
            echo "<p>Invalid credentials or incorrect access role selected.</p>";
            echo "<p><a href='../index.php'>Go Back and Try Again</a></p>";
        }

    } catch (\PDOException $e) {
        die("Database error encountered: " . $e->getMessage());
    }
} else {
    // If someone tries to access this file directly, kick them back to login page
    header("Location: ../index.php");
    exit;
}
?>
