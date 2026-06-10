<?php
// seed-system.php
header('Content-Type: text/plain');
require_once 'config/db.php';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "=== STARTING SYSTEM SEEDING ENGINE ===\n\n";

    // 1. Clean up and populate IT Categories cleanly
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("TRUNCATE TABLE categories;");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    
    $categories = ['Network Infrastructure', 'Portal & Database Access', 'Hardware & PC Equipment', 'Software Installation'];
    $cat_stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (:name)");
    foreach ($categories as $cat) {
        $cat_stmt->execute(['name' => $cat]);
        echo "✓ Category created: $cat\n";
    }
    echo "\n";

    // 2. Prepare user account data matrix
    $test_users = [
        [
            'username' => 'admin_user',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'user_role' => 'admin'
        ],
        [
            'username' => 'tech_alpha',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'user_role' => 'technician'
        ],
        [
            'username' => 'tech_bravo',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'user_role' => 'technician'
        ],
        [
            'username' => 'student_test',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'user_role' => 'client'
        ]
    ];

    // 3. Inject missing user accounts gracefully without causing duplicate key crashes
    $user_stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
    $insert_stmt = $pdo->prepare("INSERT INTO users (username, password, user_role) VALUES (:username, :password, :user_role)");

    foreach ($test_users as $user) {
        $user_stmt->execute(['username' => $user['username']]);
        if ($user_stmt->fetchColumn() == 0) {
            $insert_stmt->execute($user);
            echo "✓ User account seeded: {$user['username']} ({$user['user_role']})\n";
        } else {
            echo "ℹ User account already exists: {$user['username']}\n";
        }
    }

    echo "\n=== SYSTEM SEEDING SUCCESSFUL: CORE INVENTORIES COMPLETED ===\n";

} catch (PDOException $e) {
    echo "\nCRITICAL SEED ENGINE FAILURE: " . $e->getMessage() . "\n";
}
?>
