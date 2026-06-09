<?php
// actions/update-ticket-status.php
session_start();
require_once '../config/db.php';

// Security Guard: Block anyone who isn't an Admin or Technician
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'technician'])) {
    header("Location: ../index.php");
    exit;
}

if (isset($_GET['id']) && isset($_GET['status'])) {
    $ticket_id  = intval($_GET['id']);
    $new_status = trim($_GET['status']);

    if (in_array($new_status, ['open', 'resolved'])) {
        try {
            $stmt = $pdo->prepare("UPDATE tickets SET status = :status WHERE id = :id");
            $stmt->execute([
                'status' => $new_status,
                'id'     => $ticket_id
            ]);

            header("Location: ../manage-tickets.php?action=resolved");
            exit;
        } catch (\PDOException $e) {
            die("Critical State Change Failure: " . $e->getMessage());
        }
    }
}

header("Location: ../manage-tickets.php");
exit;
?>
