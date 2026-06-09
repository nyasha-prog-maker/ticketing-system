<?php
// submit-ticket.php
session_start();
require_once 'config/db.php';

// Security Guard: Only allow logged-in clients/students to submit tickets
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'client') {
    header("Location: index.php");
    exit;
}

try {
    // Fetch all available categories from the database to populate the dropdown dynamically
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll();
} catch (\PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Ticket - Support Desk</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { background: #f3f4f6; display: flex; min-height: 100vh; }
        
        /* Sidebar Layout */
        .sidebar { width: 260px; background: #1e3a8a; color: white; padding: 25px; display: flex; flex-direction: column; }
        .sidebar h2 { font-size: 20px; margin-bottom: 30px; text-align: center; border-bottom: 1px solid #3b82f6; padding-bottom: 15px; }
        .sidebar a { color: #d1d5db; text-decoration: none; padding: 12px; display: block; border-radius: 6px; margin-bottom: 10px; font-size: 15px; }
        .sidebar a:hover, .sidebar a.active { background: #2563eb; color: white; }
        .logout-btn { margin-top: auto; background: #dc2626 !important; text-align: center; font-weight: bold; }
        
        /* Main Content Container */
        .main-content { flex: 1; padding: 40px; }
        .form-container { background: white; padding: 35px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); max-width: 700px; margin: 0 auto; }
        .form-container h1 { color: #111827; font-size: 24px; margin-bottom: 8px; }
        .form-container p { color: #6b7280; font-size: 14px; margin-bottom: 25px; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 6px; color: #374151; font-weight: 600; font-size: 14px; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 15px; transition: border-color 0.2s;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #2563eb; }
        .form-group textarea { resize: vertical; height: 120px; }
        
        .btn-submit { padding: 12px 24px; background: #2563eb; color: white; border: none; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; transition: background 0.2s; }
        .btn-submit:hover { background: #1d4ed8; }
        .btn-cancel { display: inline-block; padding: 12px 24px; color: #4b5563; text-decoration: none; font-size: 15px; margin-left: 10px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Support Desk</h2>
        <a href="dashboard.php">🏠 Home Dashboard</a>
        <a href="submit-ticket.php" class="active">📝 Submit a Ticket</a>
        <a href="#">📋 My Ticket History</a>
        <a href="actions/logout.php" class="logout-btn">🚪 Sign Out</a>
    </div>

    <div class="main-content">
        <div class="form-container">
            <h1>Create New Support Ticket</h1>
            <p>Please provide clear details about the issue you are experiencing so our IT team can assist you efficiently.</p>
            
            <form action="actions/ticket-process.php" method="POST">
                <div class="form-group">
                    <label for="title">Issue Summary / Title</label>
                    <input type="text" id="title" name="title" required placeholder="e.g., Unable to log into Chamilo LMS platform">
                </div>

                <div class="form-group">
                    <label for="category_id">Issue Category</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">-- Select a Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="priority">Urgency / Priority Level</label>
                    <select id="priority" name="priority" required>
                        <option value="low">Low - General inquiry or minor issue</option>
                        <option value="medium" selected>Medium - Normal operational issue</option>
                        <option value="high">High - Severely affecting my work/studies</option>
                        <option value="urgent">Urgent - Complete system outage / Blocked completely</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="description">Detailed Description</label>
                    <textarea id="description" name="description" required placeholder="Provide step-by-step details of what happened, error messages, or specific recording links/folders affected..."></textarea>
                </div>

                <button type="submit" class="btn-submit">Submit Ticket</button>
                <a href="dashboard.php" class="btn-cancel">Cancel</a>
            </form>
        </div>
    </div>

</body>
</html>
