<?php
// database/seed.php
require_once '../config/db.php';

try {
    // Clear existing tables cleanly
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE users; TRUNCATE TABLE categories; SET FOREIGN_KEY_CHECKS = 1;");

    // 1. Insert Test Users
    $test_users = [
        ['username' => 'admin', 'email' => 'admin@portal.com', 'password' => password_hash('password123', PASSWORD_DEFAULT), 'role' => 'admin'],
        ['username' => 'tech1', 'email' => 'tech1@portal.com', 'password' => password_hash('password123', PASSWORD_DEFAULT), 'role' => 'technician'],
        ['username' => 'student1', 'email' => 'student1@portal.com', 'password' => password_hash('password123', PASSWORD_DEFAULT), 'role' => 'client']
    ];
    $user_stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)");
    foreach ($test_users as $user) { $user_stmt->execute($user); }

    // 2. Insert Core Operational Categories
    $categories = [
        ['name' => 'LMS & Portal Administration', 'description' => 'Issues related to Chamilo, Moodle, student enrollments, or class recording links.'],
        ['name' => 'Network Infrastructure', 'description' => 'Problems with Wi-Fi connectivity, UniFi server drops, or Ethernet wall ports.'],
        ['name' => 'Hardware & Workstations', 'description' => 'Computer lab breakdowns, printer issues, or broken projectors.'],
        ['name' => 'Software & Applications', 'description' => 'Operating system activation, XAMPP configurations, or software licensing glitches.']
    ];
    $cat_stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (:name, :description)");
    foreach ($categories as $cat) { $cat_stmt->execute($cat); }

    echo "<h1>Database Seeded Successfully! 🎉</h1>";
    echo "<p>Users and IT categories are now loaded completely.</p>";

} catch (\PDOException $e) {
    echo "Error seeding database: " . $e->getMessage();
}
?>
