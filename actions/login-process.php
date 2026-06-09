<?php
// actions/login-process.php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_input = trim($_POST['username']);
    $password_input = trim($_POST['password']);
    $role_input     = $_POST['role'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = :username OR email = :email) AND role = :role");
        $stmt->execute([
            'username' => $username_input,
            'email'    => $username_input,
            'role'     => $role_input
        ]);
        
        $user = $stmt->fetch();

        if ($user && password_verify($password_input, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['user_role'] = $user['role'];

            header("Location: ../dashboard.php");
            exit;
        } else {
            echo "<h1>Access Denied</h1>";
            echo "<p>Invalid credentials or incorrect access role selected.</p>";
            echo "<p><a href='../index.php'>Go Back and Try Again</a></p>";
        }

    } catch (\PDOException $e) {
        die("Database error encountered: " . $e->getMessage());
    }
} else {
    header("Location: ../index.php");
    exit;
}
?>
