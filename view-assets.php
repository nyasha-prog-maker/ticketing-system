<?php
// view-assets.php
session_start();
require_once 'config/db.php';

// Guard: Only Admin and Technician roles can access hardware asset databases
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'technician'])) {
    header("Location: index.php");
    exit;
}

$username = htmlspecialchars($_SESSION['username']);
$role     = $_SESSION['user_role'];

try {
    $stmt = $pdo->query("SELECT * FROM assets ORDER BY asset_tag ASC");
    $assets = $stmt->fetchAll();
} catch (\PDOException $e) {
    die("Database inventory execution error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ICT Asset Tracking - Support Desk</title>
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
        .header-area { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .container h1 { color: #111827; font-size: 24px; }
        .add-asset-btn { background: #2563eb; color: white; text-decoration: none; padding: 10px 16px; font-size: 13px; font-weight: bold; border-radius: 6px; }
        .add-asset-btn:hover { background: #1d4ed8; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 14px; border-bottom: 1px solid #e5e7eb; font-size: 14px; text-align: left; }
        th { background: #f9fafb; color: #374151; font-weight: 600; text-transform: uppercase; font-size: 12px; }
        
        .badge { padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; display: inline-block; }
        .status-active { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .status-maintenance { background: #fef9c3; color: #a16207; border: 1px solid #fef08a; }
        .status-missing { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .status-disposed { background: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb; }
        .tag-style { font-family: 'Courier New', Courier, monospace; font-weight: bold; color: #111827; background: #f3f4f6; padding: 2px 6px; border-radius:4px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Support Desk</h2>
        <a href="dashboard.php">🏠 Home Dashboard</a>
        <a href="manage-tickets.php">📂 Manage Tickets</a>
        <a href="view-assets.php" class="active">💻 View Assets</a>
        <a href="actions/logout.php" class="logout-btn">🚪 Sign Out</a>
    </div>

    <div class="main-content">
        <div class="container">
            <div class="header-area">
                <div>
                    <h1>Institutional ICT Asset Registry</h1>
                    <p style="color:#6b7280; font-size:14px; margin-top:4px;">Audit tracking configuration for equipment hardware, location matrix, and deployment status.</p>
                </div>
                <a href="#" class="add-asset-btn">+ Register New Asset</a>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th style="width: 12%;">Asset Tag</th>
                        <th style="width: 28%;">Equipment Model Name</th>
                        <th style="width: 15%;">Device Type</th>
                        <th style="width: 18%;">Serial Number</th>
                        <th style="width: 15%;">Current Location</th>
                        <th style="width: 12%;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assets as $asset): ?>
                        <tr>
                            <td><span class="tag-style"><?php echo htmlspecialchars($asset['asset_tag']); ?></span></td>
                            <td><strong><?php echo htmlspecialchars($asset['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($asset['type']); ?></td>
                            <td><span style="color:#4b5563; font-size:13px;"><?php echo htmlspecialchars($asset['serial_number'] ?: 'N/A'); ?></span></td>
                            <td>📍 <?php echo htmlspecialchars($asset['location']); ?></td>
                            <td>
                                <span class="badge status-<?php echo $asset['status']; ?>">
                                    <?php echo $asset['status']; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
