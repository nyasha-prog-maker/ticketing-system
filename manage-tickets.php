<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'technician'])) {
    header("Location: index.php");
    exit;
}

$username = htmlspecialchars($_SESSION['username']);
$current_role = $_SESSION['user_role'];
$current_user_id = $_SESSION['user_id'];

try {
    $tech_stmt = $pdo->query("SELECT id, username FROM users WHERE user_role = 'technician' ORDER BY username ASC");
    $technicians = $tech_stmt->fetchAll();

    if ($current_role === 'technician') {
        $stmt = $pdo->prepare("
            SELECT tickets.*, 
                   u1.username AS student_name, 
                   u2.username AS tech_name,
                   categories.name AS category_name 
            FROM tickets 
            LEFT JOIN users u1 ON tickets.client_id = u1.id 
            LEFT JOIN users u2 ON tickets.assigned_to = u2.id
            LEFT JOIN categories ON tickets.category_id = categories.id 
            WHERE tickets.assigned_to = :tech_id
            ORDER BY tickets.id DESC
        ");
        $stmt->execute(['tech_id' => $current_user_id]);
    } else {
        $stmt = $pdo->query("
            SELECT tickets.*, 
                   u1.username AS student_name, 
                   u2.username AS tech_name,
                   categories.name AS category_name 
            FROM tickets 
            LEFT JOIN users u1 ON tickets.client_id = u1.id 
            LEFT JOIN users u2 ON tickets.assigned_to = u2.id
            LEFT JOIN categories ON tickets.category_id = categories.id 
            ORDER BY tickets.id DESC
        ");
    }
    $tickets = $stmt->fetchAll();
} catch (\PDOException $e) {
    die("Database engine error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Tickets - Support Desk</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { background: #f3f4f6; display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: #1e3a8a; color: white; padding: 25px; display: flex; flex-direction: column; }
        .sidebar h2 { font-size: 20px; margin-bottom: 30px; text-align: center; border-bottom: 1px solid #3b82f6; padding-bottom: 15px; }
        .sidebar a { color: #d1d5db; text-decoration: none; padding: 12px; display: block; border-radius: 6px; margin-bottom: 10px; font-size: 15px; }
        .sidebar a:hover, .sidebar a.active { background: #2563eb; color: white; }
        .logout-btn { margin-top: auto; background: #dc2626 !important; text-align: center; font-weight: bold; }
        .main-content { flex: 1; padding: 40px; }
        .container { background: white; padding: 35px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .container h1 { color: #111827; font-size: 24px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 14px; border-bottom: 1px solid #e5e7eb; font-size: 14px; text-align: left; }
        th { background: #f9fafb; color: #374151; font-weight: 600; text-transform: uppercase; font-size: 12px; }
        .badge { padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; display: inline-block; }
        .status-resolved { background: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb; }
        .btn-resolve { background: #10b981; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-weight: bold; cursor: pointer; text-decoration: none; font-size: 12px; display: inline-block; margin-top: 5px; }
        .btn-resolve:hover { background: #059669; }
        .description-text { font-size: 13px; color: #4b5563; margin-top: 5px; display: block; background: #f9fafb; padding: 8px; border-radius: 4px; border-left: 3px solid #d1d5db; }
        .resolution-box { font-size: 13px; color: #065f46; margin-top: 5px; display: block; background: #ecfdf5; padding: 8px; border-radius: 4px; border-left: 3px solid #10b981; }
        .attachment-link { display: inline-block; margin-top: 8px; font-size: 12px; color: #2563eb; text-decoration: none; font-weight: bold; background: #e0f2fe; padding: 4px 8px; border-radius: 4px; }
        .asset-badge { display: inline-block; background: #f3f4f6; color: #111827; font-family: monospace; font-weight: bold; padding: 2px 6px; border-radius: 4px; border: 1px solid #d1d5db; font-size: 11px; margin-bottom: 5px; }
        .assign-form { display: flex; gap: 6px; margin-top: 5px; }
        .assign-select { padding: 4px 8px; font-size: 12px; border: 1px solid #d1d5db; border-radius: 4px; background: #fff; }
        .btn-assign { background: #2563eb; color: white; border: none; padding: 4px 8px; font-size: 12px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .assignment-info { font-size: 12px; color: #4b5563; display: block; margin-bottom: 5px; }
        .success-alert { background: #dcfce7; border-left: 5px solid #15803d; color: #166534; padding: 15px; margin-bottom: 25px; border-radius: 4px; font-size: 15px; }
        .no-data { text-align: center; color: #6b7280; font-style: italic; padding: 20px 0; }
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
        <?php if (isset($_GET['action']) && $_GET['action'] === 'assigned'): ?>
            <div class="success-alert">✓ <strong>Workload Updated:</strong> Ticket ownership assignment successfully updated.</div>
        <?php endif; ?>
        <?php if (isset($_GET['action']) && $_GET['action'] === 'resolved'): ?>
            <div class="success-alert">✓ <strong>Incident Closed:</strong> Ticket marked resolved and closure logs recorded securely.</div>
        <?php endif; ?>

        <div class="container">
            <h1><?php echo ($current_role === 'admin') ? 'Global Ticket Management Console' : 'My Personal Task Queue'; ?></h1>
            <p>Review active system incidents, manage infrastructure specifications, and track resolution audits.</p>
            
            <table>
                <thead>
                    <tr>
                        <th style="width: 8%;">ID</th>
                        <th style="width: 15%;">Submitted By</th>
                        <th style="width: 35%;">Issue Details</th>
                        <th style="width: 12%;">Category</th>
                        <th style="width: 18%;">Task Assignment</th>
                        <th style="width: 12%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tickets)): ?>
                        <tr>
                            <td colspan="6" class="no-data">No active support tickets found in this scope.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td>#<?php echo $ticket['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($ticket['student_name']); ?></strong></td>
                            <td>
                                <?php if (!empty($ticket['asset_tag'])): ?>
                                    <div class="asset-badge">💻 Tag: <?php echo htmlspecialchars($ticket['asset_tag']); ?></div><br>
                                <?php endif; ?>
                                
                                <strong><?php echo htmlspecialchars($ticket['title']); ?></strong>
                                <span class="description-text"><?php echo htmlspecialchars($ticket['description']); ?></span>
                                
                                <?php if (!empty($ticket['resolution_notes'])): ?>
                                    <span class="resolution-box"><strong>🔧 Resolution Log:</strong> <?php echo htmlspecialchars($ticket['resolution_notes']); ?></span>
                                <?php endif; ?>

                                <?php if (!empty($ticket['screenshot_path'])): ?>
                                    <a href="<?php echo htmlspecialchars($ticket['screenshot_path']); ?>" target="_blank" class="attachment-link">🖼️ View Screenshot</a>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($ticket['category_name'] ?? 'Uncategorized'); ?></td>
                            <td>
                                <span class="assignment-info">
                                    👤 Assigned: <strong><?php echo htmlspecialchars($ticket['tech_name'] ?? 'Unassigned'); ?></strong>
                                </span>
                                <?php if ($current_role === 'admin' && $ticket['status'] === 'open'): ?>
                                    <form method="POST" action="actions/assign-ticket.php" class="assign-form">
                                        <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                        <select name="technician_id" class="assign-select">
                                            <option value="">-- Nobody --</option>
                                            <?php foreach ($technicians as $tech): ?>
                                                <option value="<?php echo $tech['id']; ?>" <?php echo ($ticket['assigned_to'] == $tech['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($tech['username']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn-assign">Assign</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($ticket['status'] === 'open'): ?>
                                    <a href="resolve-ticket.php?id=<?php echo $ticket['id']; ?>" class="btn-resolve">Mark Resolved</a>
                                <?php else: ?>
                                    <span class="badge status-resolved">✓ Resolved</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
