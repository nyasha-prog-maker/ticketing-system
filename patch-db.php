<?php
require_once 'config/db.php';
try {
    $pdo->exec("ALTER TABLE tickets ADD COLUMN screenshot_path VARCHAR(255) NULL DEFAULT NULL AFTER description");
    echo "Database updated successfully!\n";
} catch (\PDOException $e) {
    // Ignore error if column already exists
    echo "Column already exists or database error.\n";
}
unlink(__FILE__); // Self-destruct for cleanliness
?>
