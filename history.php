<?php
// history.php
session_start();
require_once 'config/db.php';

// Security Guard: Only allow logged-in clients/students to view this page
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'client') {
    header("Location: index.php");
    exit;
}

$username = htmlspecialchars($_SESSION['username']);
$client_id = $_SESSION['user_id'];

try {
    // Fetch all tickets filed by this specific client, joining with categories for the display name
    $stmt = $pdo->prepare("
        SELECT tickets.*, categories.name AS category_name 
        FROM tickets 
        LEFT JOIN categories ON tickets.category_id = categories.id 
        WHERE tickets.client_id = :client_id 
        ORDER BY tickets.id DESC
    ");
    $stmt->execute(['client_id' => $client_id]);
    $tickets = $stmt->fetchAll();
} catch (\PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Ticket History - Support Desk</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { background: #f3f4f6; display: flex; min-height: 100vh; }
        
        /* Sidebar Layout */
        .sidebar { width: 260px; background: #1e3a8a; color: white; padding: 25px; display: flex; flex-direction: column; }
        .sidebar h2 { font-size: 20px; margin-bottom: 30px; text-align: center; border-bottom: 1px solid #3b82f6; padding-bottom: 15px; }
        .sidebar a { color: #d1d5db; text-decoration: none; padding: 12px; display: block; border-radius: 6px; margin-bottom: 10px; font-size: 15px; }
        .sidebar a:hover, .sidebar a.active { background: #2563eb; color: white; }
        .logout-btn { margin-top: auto; background: #dc2626 !important; text-align: center; font-weight: bold; }
        
        /* Main Content Workspace */
        .main-content { flex: 1; padding: 40px; }
        .container { background: white; padding: 35px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .container h1 { color: #111827; font-size: 24px; margin-bottom: 10px; }
        .container p { color: #6b7280; font-size: 14px; margin-bottom: 25px; }
        
        /* Data Table Styling */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; text-align: left; }
        th, td { padding: 14px; border-bottom: 1px solid #e5e7eb; font-size: 15px; }
        th { background: #f9fafb; color: #374151; font-weight: 600; text-transform: uppercase; font-size: 13px; letter-spacing: 0.5px; }
        tr:hover { background: #fcfdfd; }
        
        /* Dynamic Badges */
        .badge { padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; text-transform: uppercase; display: inline-block; }
        .priority-low { background: #f3f4f6; color: #4b5563; }
        .priority-medium { background: #e0f2fe; color: #0369a1; }
        .priority-high { background: #ffedd5; color: #c2410c; }
        .priority-urgent { background: #fee2e2; color: #991b1b; }
        
        .status-open { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .status-resolved { background: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb; }
        
        .no-data { text-align: center; color: #9ca3af; padding: 40px 0; font-style: italic; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Support Desk</h2>
        <a href="dashboard.php">🏠 Home Dashboard</a>
        <a href="submit-ticket.php">📝 Submit a Ticket</a>
        <a href="history.php" class="active">📋 My Ticket History</a>
        <a href="actions/logout.php" class="logout-btn">🚪 Sign Out</a>
    </div>

    <div class="main-content">
        <div class="container">
            <h1>Your Support Ticket History</h1>
            <p>Track the live investigation status and assignment tracking details for all your filed requests.</p>
            
            <?php if (empty($tickets)): ?>
                <div class="no-data">
                    <p>You haven't submitted any support tickets yet. Click "Submit a Ticket" to create one.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Issue Summary</th>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $ticket): ?>
                            <tr>
                                <td>#<?php echo $ticket['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($ticket['title']); ?></strong></td>
                                <td><?php echo htmlspecialchars($ticket['category_name'] ?? 'Uncategorized'); ?></td>
                                <td>
                                    <span class="badge priority-<?php echo $ticket['priority']; ?>">
                                        <?php echo $ticket['priority']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge status-<?php echo $ticket['status']; ?>">
                                        ● <?php echo $ticket['status']; ?>
                                    </span>
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
