<?php
// database/seed.php
require_once '../config/db.php';

try {
    // Clear any existing users to prevent duplicate errors if rerun
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE users; SET FOREIGN_KEY_CHECKS = 1;");

    // Define test accounts with securely hashed passwords
    $test_users = [
        [
            'username' => 'admin',
            'email' => 'admin@portal.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'admin'
        ],
        [
            'username' => 'tech1',
            'email' => 'tech1@portal.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'technician'
        ],
        [
            'username' => 'student1',
            'email' => 'student1@portal.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'client'
        ]
    ];

    // Prepare SQL Statement
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)");

    // Insert each user
    foreach ($test_users as $user) {
        $stmt->execute($user);
    }

    echo "<h1>Database Seeded Successfully! 🎉</h1>";
    echo "<p>The following test accounts have been created:</p>";
    echo "<ul>
            <li><strong>Admin:</strong> username: <code>admin</code> | password: <code>password123</code></li>
            <li><strong>Technician:</strong> username: <code>tech1</code> | password: <code>password123</code></li>
            <li><strong>Client/Student:</strong> username: <code>student1</code> | password: <code>password123</code></li>
          </ul>";

} catch (\PDOException $e) {
    echo "Error seeding database: " . $e->getMessage();
}
?>
