<?php
// resolve-ticket.php
session_start();
require_once 'config/db.php';

// Guard: Only technicians and admins can close tickets
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'technician'])) {
    header("Location: index.php");
    exit;
}

$ticket_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$error_msg = '';

// Fetch the targeted ticket details
try {
    $stmt = $pdo->prepare("
        SELECT tickets.*, users.username AS student_name, categories.name AS category_name 
        FROM tickets 
        LEFT JOIN users ON tickets.client_id = users.id 
        LEFT JOIN categories ON tickets.category_id = categories.id 
        WHERE tickets.id = :id
    ");
    $stmt->execute(['id' => $ticket_id]);
    $ticket = $stmt->fetch();

    if (!$ticket) {
        die("Error: Ticket reference target not found.");
    }
} catch (\PDOException $e) {
    die("Database failure: " . $e->getMessage());
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resolution_notes = trim($_POST['resolution_notes']);

    if (empty($resolution_notes)) {
        $error_msg = "Please fill out the resolution notes describing the root fix.";
    } else {
        try {
            $update_stmt = $pdo->prepare("
                UPDATE tickets 
                SET status = 'resolved', 
                    resolution_notes = :notes, 
                    resolved_at = CURRENT_TIMESTAMP 
                WHERE id = :id
            ");
            $update_stmt->execute([
                'notes' => $resolution_notes,
                'id'    => $ticket_id
            ]);

            header("Location: manage-tickets.php?action=resolved");
            exit;
        } catch (\PDOException $e) {
            $error_msg = "Failed to update record state: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Resolve Ticket #<?php echo $ticket['id']; ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { background: #f3f4f6; display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: #1e3a8a; color: white; padding: 25px; display: flex; flex-direction: column; }
        .sidebar h2 { font-size: 20px; margin-bottom: 30px; text-align: center; border-bottom: 1px solid #3b82f6; padding-bottom: 15px; }
        .sidebar a { color: #d1d5db; text-decoration: none; padding: 12px; display: block; border-radius: 6px; margin-bottom: 10px; font-size: 15px; }
        .sidebar a:hover, .sidebar a.active { background: #2563eb; color: white; }
        .logout-btn { margin-top: auto; background: #dc2626 !important; text-align: center; font-weight: bold; }
        
        .main-content { flex: 1; padding: 40px; display: flex; justify-content: center; }
        .resolution-card { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); width: 100%; max-width: 700px; }
        .resolution-card h1 { color: #111827; font-size: 22px; margin-bottom: 15px; }
        
        .incident-summary { background: #f9fafb; border: 1px solid #e5e7eb; padding: 20px; border-radius: 6px; margin-bottom: 25px; font-size: 14px; }
        .incident-summary p { margin-bottom: 10px; color: #4b5563; }
        .incident-summary strong { color: #111827; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 8px; color: #374151; font-size: 14px; }
        textarea { width: 100%; height: 140px; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 15px; resize: vertical; }
        
        .btn-group { display: flex; gap: 12px; }
        .btn-submit { background: #10b981; color: white; border: none; padding: 12px 24px; font-size: 14px; font-weight: bold; border-radius: 6px; cursor: pointer; flex: 1; }
        .btn-submit:hover { background: #059669; }
        .btn-back { background: #f3f4f6; color: #4b5563; text-decoration: none; padding: 12px 24px; font-size: 14px; font-weight: bold; border-radius: 6px; text-align: center; flex: 1; border: 1px solid #e5e7eb; }
        .btn-back:hover { background: #e5e7eb; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 4px; font-size: 14px; margin-bottom: 20px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Support Desk</h2>
        <a href="dashboard.php">🏠 Home Dashboard</a>
        <a href="manage-tickets.php" class="active">📂 Manage Tickets</a>
        <a href="view-assets.php">💻 View Assets</a>
        <a href="view-reports.php">📊 View Reports</a>
        <a href="actions/logout.php" class="logout-btn">🚪 Sign Out</a>
    </div>

    <div class="main-content">
        <div class="resolution-card">
            <h1>Log Ticket Resolution Details</h1>
            
            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-error">⚠️ <?php echo $error_msg; ?></div>
            <?php endif; ?>

            <div class="incident-summary">
                <p><strong>Ticket ID:</strong> #<?php echo $ticket['id']; ?></p>
                <p><strong>Submitted By:</strong> <?php echo htmlspecialchars($ticket['student_name']); ?></p>
                <p><strong>IT Category:</strong> <?php echo htmlspecialchars($ticket['category_name'] ?? 'General'); ?></p>
                <p><strong>Issue Title:</strong> <?php echo htmlspecialchars($ticket['title']); ?></p>
                <p style="margin-bottom: 0; padding-top: 8px; border-top: 1px solid #e5e7eb;">
                    <strong>Problem Description:</strong><br>
                    <span style="font-style: italic; color:#1f2937;"><?php echo nl2br(htmlspecialchars($ticket['description'])); ?></span>
                </p>
            </div>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="resolution_notes">Technical Diagnostics & Resolution Actions taken *</label>
                    <textarea id="resolution_notes" name="resolution_notes" placeholder="Detail what actions were taken to resolve this system error (e.g., cleared system cache, routed network wiring to backup port, verified Chamilo user context mapping)..." required></textarea>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn-submit">Commit Fix & Resolve Ticket</button>
                    <a href="manage-tickets.php" class="btn-back">Return to Console</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
