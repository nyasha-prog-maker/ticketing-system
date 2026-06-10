<?php
require_once 'config/db.php';

try {
    // Set PDO to throw exceptions clearly
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec("ALTER TABLE tickets ADD COLUMN screenshot_path VARCHAR(255) NULL DEFAULT NULL AFTER description");
    echo "SUCCESS: 'screenshot_path' column has been successfully injected into the tickets table!\n";
} catch (PDOException $e) {
    echo "ERROR RUNNING SQL: " . $e->getMessage() . "\n";
}
unlink(__FILE__);
?>
