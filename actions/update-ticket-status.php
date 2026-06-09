<?php
// actions/update-ticket-status.php
session_start();
require_once '../config/db.php';

// Security Guard: Block anyone who isn't a staff member (Admin/Tech)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'technician'])) {
    header("Location: ../index.php");
    exit;
}

// Intercept incoming URL GET payload parameters
if (isset($_GET['id']) && isset($_GET['status'])) {
    $ticket_id  = intval($_GET['id']);
    $new_status = trim($_GET['status']);

    // Ensure the state instruction is completely valid
    if (in_array($new_status, ['open', 'resolved'])) {
        try {
            // Prepare transactional SQL data transformation update
            $stmt = $pdo->prepare("UPDATE tickets SET status = :status WHERE id = :id");
            $stmt->execute([
                'status' => $new_status,
                'id'     => $ticket_id
            ]);

            // Route back successfully with operational confirmation alert flag
            header("Location: ../manage-tickets.php?action=resolved");
            exit;
        } catch (\PDOException $e) {
            die("Critical State Change Failure: " . $e->getMessage());
        }
    }
}

// Redirect fallback error security loop
header("Location: ../manage-tickets.php");
exit;
?>
