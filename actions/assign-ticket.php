<?php
// actions/assign-ticket.php
session_start();
require_once '../config/db.php';

// Security Guard: Only Admins can delegate workload assignments
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_id     = intval($_POST['ticket_id']);
    $technician_id = !empty($_POST['technician_id']) ? intval($_POST['technician_id']) : null;

    try {
        $stmt = $pdo->prepare("UPDATE tickets SET assigned_to = :technician_id WHERE id = :id");
        $stmt->execute([
            'technician_id' => $technician_id,
            'id'            => $ticket_id
        ]);

        header("Location: ../manage-tickets.php?action=assigned");
        exit;
    } catch (\PDOException $e) {
        die("Critical Assignment Failure: " . $e->getMessage());
    }
}

header("Location: ../manage-tickets.php");
exit;
?>
