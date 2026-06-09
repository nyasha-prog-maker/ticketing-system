<?php
// actions/ticket-process.php
session_start();
require_once '../config/db.php';

// Security Guard: Ensure only logged-in clients can submit data
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'client') {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Gather and clean incoming text inputs
    $title       = trim($_POST['title']);
    $category_id = intval($_POST['category_id']);
    $priority    = trim($_POST['priority']);
    $description = trim($_POST['description']);
    $client_id   = $_SESSION['user_id']; // Captured directly from secure session

    // Basic Validation check
    if (empty($title) || empty($category_id) || empty($priority) || empty($description)) {
        die("Error: All fields are mandatory to file a support request.");
    }

    try {
        // 2. Prepare the secure SQL query to insert into the tickets table
        $stmt = $pdo->prepare("
            INSERT INTO tickets (title, description, client_id, category_id, priority, status) 
            VALUES (:title, :description, :client_id, :category_id, :priority, 'open')
        ");
        
        // 3. Execute with safe parameterized array mapping
        $stmt->execute([
            'title'       => $title,
            'description' => $description,
            'client_id'   => $client_id,
            'category_id' => $category_id,
            'priority'    => $priority
        ]);

        // 4. Smooth Redirect back to the dashboard with a success marker parameter
        header("Location: ../dashboard.php?status=ticket_created");
        exit;

    } catch (\PDOException $e) {
        die("Database failed to record your ticket: " . $e->getMessage());
    }
} else {
    // Kick anyone trying to look at this file back to the form page
    header("Location: ../submit-ticket.php");
    exit;
}
?>
