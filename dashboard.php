<?php
// dashboard.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$username = htmlspecialchars($_SESSION['username']);
$role     = $_SESSION['user_role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Ticketing System</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { background: #f3f4f6; display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: #1e3a8a; color: white; padding: 25px; display: flex; flex-direction: column; }
        .sidebar h2 { font-size: 20px; margin-bottom: 30px; text-align: center; border-bottom: 1px solid #3b82f6; padding-bottom: 15px; }
        .sidebar a { color: #d1d5db; text-decoration: none; padding: 12px; display: block; border-radius: 6px; margin-bottom: 10px; font-size: 15px; }
        .sidebar a:hover, .sidebar a.active { background: #2563eb; color: white; }
        .logout-btn { margin-top: auto; background: #dc2626 !important; text-align: center; font-weight: bold; }
        .main-content { flex: 1; padding: 40px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .welcome-msg h1 { color: #111827; font-size: 24px; }
        .badge { padding: 6px 12px; border-radius: 50px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .badge-admin { background: #fef3c7; color: #d97706; }
        .badge-technician { background: #e0f2fe; color: #0369a1; }
        .badge-client { background: #dcfce7; color: #15803d; }
        .card-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .card h3 { color: #4b5563; font-size: 14px; text-transform: uppercase; margin-bottom: 10px; }
        .card p { font-size: 28px; font-weight: bold; color: #111827; }
        .action-zone { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-top: 20px; }
        .btn-primary { display: inline-block; padding: 12px 24px; background: #2563eb; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; transition: background 0.2s; }
        .btn-primary:hover { background: #1d4ed8; }
        
        /* Success Banner Style */
        .success-alert { background: #dcfce7; border-left: 5px solid #15803d; color: #166534; padding: 15px; margin-bottom: 25px; border-radius: 4px; font-size: 15px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Support Desk</h2>
        <a href="dashboard.php" class="active">🏠 Home Dashboard</a>
        <?php if ($role === 'client'): ?>
            <a href="submit-ticket.php">📝 Submit a Ticket</a>
            <a href="#">📋 My Ticket History</a>
        <?php else: ?>
            <a href="#">📂 Manage Tickets</a>
            <a href="#">📊 View Reports</a>
        <?php endif; ?>
        <a href="actions/logout.php" class="logout-btn">🚪 Sign Out</a>
    </div>

    <div class="main-content">
        
        <?php if (isset($_GET['status']) && $_GET['status'] === 'ticket_created'): ?>
            <div class="success-alert">
                🎉 <strong>Success!</strong> Your support ticket has been recorded and routed to our IT team. You can track its progress here.
            </div>
        <?php endif; ?>

        <div class="header">
            <div class="welcome-msg">
                <h1>Welcome Back, <?php echo $username; ?>!</h1>
            </div>
            <div>
                <span class="badge badge-<?php echo $role; ?>"><?php echo $role; ?> Account</span>
            </div>
        </div>

        <?php if ($role === 'admin'): ?>
            <h2>System-Wide Console Management</h2>
            <div class="card-grid">
                <div class="card"><h3>Total Open Tickets</h3><p>24</p></div>
                <div class="card"><h3>Active Technicians</h3><p>5</p></div>
                <div class="card"><h3>System Performance</h3><p>99.8%</p></div>
            </div>
        <?php elseif ($role === 'technician'): ?>
            <h2>Technician Assignment Workspace</h2>
            <div class="card-grid">
                <div class="card"><h3>Assigned To Me</h3><p>4</p></div>
                <div class="card"><h3>Pending Verification</h3><p>2</p></div>
                <div class="card"><h3>My Resolved Tickets</h3><p>18</p></div>
            </div>
        <?php else: ?>
            <div class="action-zone">
                <h2>Need IT Assistance?</h2>
                <p style="color: #6b7280; margin: 10px 0 20px 0;">Submit a ticket for portal lockouts, network connection drops, or equipment issues, and our team will get right on it.</p>
                <a href="submit-ticket.php" class="btn-primary">Create New Support Ticket</a>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>
