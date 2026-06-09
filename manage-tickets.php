<?php
// manage-tickets.php
session_start();
require_once 'config/db.php';

// Security Guard: Only allow logged-in Admins or Technicians
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'technician'])) {
    header("Location: index.php");
    exit;
}

$username = htmlspecialchars($_SESSION['username']);
$role     = $_SESSION['user_role'];

try {
    // Relational query gathering ticket details, the filing student's name, and the category name
    $stmt = $pdo->query("
        SELECT tickets.*, users.username AS student_name, categories.name AS category_name 
        FROM tickets 
        LEFT JOIN users ON tickets.client_id = users.id 
        LEFT JOIN categories ON tickets.category_id = categories.id 
        ORDER BY tickets.id DESC
    ");
    $tickets = $stmt->fetchAll();
} catch (\PDOException $e) {
    die("Database engine error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tickets - Support Desk</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { background: #f3f4f6; display: flex; min-height: 100vh; }
        
        /* Sidebar Layout */
        .sidebar { width: 260px; background: #1e3a8a; color: white; padding: 25px; display: flex; flex-direction: column; }
        .sidebar h2 { font-size: 20px; margin-bottom: 30px; text-align: center; border-bottom: 1px solid #3b82f6; padding-bottom: 15px; }
        .sidebar a { color: #d1d5db; text-decoration: none; padding: 12px; display: block; border-radius: 6px; margin-bottom: 10px; font-size: 15px; }
        .sidebar a:hover, .sidebar a.active { background: #2563eb; color: white; }
        .logout-btn { margin-top: auto; background: #dc2626 !important; text-align: center; font-weight: bold; }
        
        /* Workspace Content Layout */
        .main-content { flex: 1; padding: 40px; }
        .container { background: white; padding: 35px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .container h1 { color: #111827; font-size: 24px; margin-bottom: 8px; }
        .container p { color: #6b7280; font-size: 14px; margin-bottom: 25px; }
        
        /* Interactive Data Table Layout */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 14px; border-bottom: 1px solid #e5e7eb; font-size: 14px; text-align: left; }
        th { background: #f9fafb; color: #374151; font-weight: 600; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; }
        tr:hover { background: #fbfcfd; }
        
        /* Badges */
        .badge { padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; display: inline-block; }
        .priority-low { background: #f3f4f6; color: #4b5563; }
        .priority-medium { background: #e0f2fe; color: #0369a1; }
        .priority-high { background: #ffedd5; color: #c2410c; }
        .priority-urgent { background: #fee2e2; color: #991b1b; }
        
        .status-open { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .status-resolved { background: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb; }
        
        /* Actions Panel Elements */
        .btn-resolve { background: #10b981; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-weight: bold; cursor: pointer; text-decoration: none; font-size: 12px; transition: background 0.2s; }
        .btn-resolve:hover { background: #059669; }
        .description-text { font-size: 13px; color: #4b5563; margin-top: 5px; display: block; background: #f9fafb; padding: 8px; border-radius: 4px; border-left: 3px solid #d1d5db; }
        .success-alert { background: #dcfce7; border-left: 5px solid #15803d; color: #166534; padding: 15px; margin-bottom: 25px; border-radius: 4px; font-size: 15px; }
        .no-data { text-align: center; color: #9ca3af; padding: 40px 0; font-style: italic; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Support Desk</h2>
        <a href="dashboard.php">🏠 Home Dashboard</a>
        <a href="manage-tickets.php" class="active">📂 Manage Tickets</a>
        <a href="#">📊 View Reports</a>
        <a href="actions/logout.php" class="logout-btn">🚪 Sign Out</a>
    </div>

    <div class="main-content">
        
        <?php if (isset($_GET['action']) && $_GET['action'] === 'resolved'): ?>
            <div class="success-alert">
                ✓ <strong>Ticket Updated:</strong> Support issue status successfully switched to <strong>Resolved</strong>.
            </div>
        <?php endif; ?>

        <div class="container">
            <h1>Global Ticket Management Console</h1>
            <p>Review active incidents, technical system failure logs, and resolve issues reported by students and staff.</p>
            
            <?php if (empty($tickets)): ?>
                <div class="no-data">
                    <p>No support requests have been filed in the system yet.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 8%;">ID</th>
                            <th style="width: 15%;">Submitted By</th>
                            <th style="width: 40%;">Issue Details</th>
                            <th style="width: 15%;">Category</th>
                            <th style="width: 10%;">Priority</th>
                            <th style="width: 12%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $ticket): ?>
                            <tr>
                                <td>#<?php echo $ticket['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($ticket['student_name']); ?></strong></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($ticket['title']); ?></strong>
                                    <span class="description-text"><?php echo htmlspecialchars($ticket['description']); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($ticket['category_name'] ?? 'Uncategorized'); ?></td>
                                <td>
                                    <span class="badge priority-<?php echo $ticket['priority']; ?>">
                                        <?php echo $ticket['priority']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($ticket['status'] === 'open'): ?>
                                        <a href="actions/update-ticket-status.php?id=<?php echo $ticket['id']; ?>&status=resolved" class="btn-resolve">Mark Resolved</a>
                                    <?php else: ?>
                                        <span class="badge status-resolved">✓ Resolved</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
