<?php
// view-reports.php
session_start();
require_once 'config/db.php';

// Guard: Only Admin and Technician roles can run administrative analytical reports
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'technician'])) {
    header("Location: index.php");
    exit;
}

$username = htmlspecialchars($_SESSION['username']);
$role     = $_SESSION['user_role'];

try {
    // 1. Core Summary Metrics
    $total_tickets = $pdo->query("SELECT COUNT(*) FROM tickets")->fetchColumn();
    $open_tickets  = $pdo->query("SELECT COUNT(*) FROM tickets WHERE status = 'open'")->fetchColumn();
    $resolved_tickets = $pdo->query("SELECT COUNT(*) FROM tickets WHERE status = 'resolved'")->fetchColumn();
    
    // 2. Breakdown by Category
    $cat_stmt = $pdo->query("
        SELECT c.name AS label, COUNT(t.id) AS total 
        FROM categories c 
        LEFT JOIN tickets t ON t.category_id = c.id 
        GROUP BY c.id
    ");
    $report_categories = $cat_stmt->fetchAll();

    // 3. Breakdown by Priority
    $priority_stmt = $pdo->query("
        SELECT priority AS label, COUNT(*) AS total 
        FROM tickets 
        GROUP BY priority
    ");
    $report_priorities = $priority_stmt->fetchAll();

    // 4. Technician Workload Distribution
    $tech_stmt = $pdo->query("
        SELECT u.username AS label, COUNT(t.id) AS total 
        FROM users u 
        INNER JOIN tickets t ON t.assigned_to = u.id 
        WHERE t.status = 'open'
        GROUP BY u.id
    ");
    $report_tech_workload = $tech_stmt->fetchAll();

} catch (\PDOException $e) {
    die("Analytics Engine Compilation Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Analytics & Reports - Support Desk</title>
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
        .container h1 { color: #111827; font-size: 24px; margin-bottom: 4px; }
        
        /* Top Summary KPIs */
        .kpi-grid { display: flex; gap: 20px; margin: 25px 0; }
        .kpi-card { flex: 1; padding: 20px; border-radius: 6px; color: white; background: #1e293b; }
        .kpi-card.blue { background: #2563eb; }
        .kpi-card.amber { background: #d97706; }
        .kpi-card.emerald { background: #059669; }
        .kpi-card h3 { font-size: 12px; text-transform: uppercase; opacity: 0.85; letter-spacing: 0.5px; }
        .kpi-card p { font-size: 28px; font-weight: bold; margin-top: 5px; }

        /* Report Matrix Grid Layout */
        .report-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; margin-top: 30px; }
        .report-box { border: 1px solid #e5e7eb; border-radius: 6px; padding: 20px; background: #f9fafb; }
        .report-box h2 { font-size: 16px; color: #1e3a8a; margin-bottom: 15px; border-bottom: 2px solid #e5e7eb; padding-bottom: 8px; }
        
        .data-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px dashed #e5e7eb; font-size: 14px; }
        .data-row:last-child { border-bottom: none; }
        .count-tag { background: #e2e8f0; color: #1e293b; font-weight: bold; padding: 2px 8px; border-radius: 20px; font-size: 12px; }
        .no-data { color: #6b7280; font-style: italic; font-size: 14px; text-align: center; padding: 10px 0; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Support Desk</h2>
        <a href="dashboard.php">🏠 Home Dashboard</a>
        <a href="manage-tickets.php">📂 Manage Tickets</a>
        <a href="view-assets.php">💻 View Assets</a>
        <a href="view-reports.php" class="active">📊 View Reports</a>
        <a href="actions/logout.php" class="logout-btn">🚪 Sign Out</a>
    </div>

    <div class="main-content">
        <div class="container">
            <h1>System Performance Reports</h1>
            <p style="color:#6b7280; font-size:14px;">Real-time analytical data regarding operational health metrics and staff workloads.</p>
            
            <div class="kpi-grid">
                <div class="kpi-card blue">
                    <h3>Total Logged Issues</h3>
                    <p><?php echo $total_tickets; ?></p>
                </div>
                <div class="kpi-card amber">
                    <h3>Unresolved Open State</h3>
                    <p><?php echo $open_tickets; ?></p>
                </div>
                <div class="kpi-card emerald">
                    <h3>Successfully Resolved</h3>
                    <p><?php echo $resolved_tickets; ?></p>
                </div>
            </div>

            <div class="report-grid">
                <div class="report-box">
                    <h2>Incidents by IT Category</h2>
                    <?php if (empty($report_categories)): ?><p class="no-data">No recorded data available.</p><?php endif; ?>
                    <?php foreach ($report_categories as $row): ?>
                        <div class="data-row">
                            <span>📂 <?php echo htmlspecialchars($row['label'] ?? 'Uncategorized'); ?></span>
                            <span class="count-tag"><?php echo $row['total']; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="report-box">
                    <h2>Incidents by Priority Matrix</h2>
                    <?php if (empty($report_priorities)): ?><p class="no-data">No recorded data available.</p><?php endif; ?>
                    <?php foreach ($report_priorities as $row): ?>
                        <div class="data-row">
                            <span>⚠️ Criticality: <strong><?php echo ucfirst(htmlspecialchars($row['label'])); ?></strong></span>
                            <span class="count-tag"><?php echo $row['total']; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="report-box" style="grid-column: span 2;">
                    <h2>Staff Workload Distribution (Active Pending Tickets Assigned)</h2>
                    <?php if (empty($report_tech_workload)): ?><p class="no-data">No technicians currently holding pending tickets.</p><?php endif; ?>
                    <?php foreach ($report_tech_workload as $row): ?>
                        <div class="data-row">
                            <span>👤 Technician Officer: <strong><?php echo htmlspecialchars($row['label']); ?></strong></span>
                            <span class="count-tag" style="background:#2563eb; color:white;"><?php echo $row['total']; ?> active tasks</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
