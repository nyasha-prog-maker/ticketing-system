<?php
// submit-ticket.php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'client') {
    header("Location: index.php");
    exit;
}

$error = '';

try {
    // 1. Fetch categories for the dropdown
    $categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
    
    // 2. Fetch active hardware assets for the equipment tracking dropdown
    $assets = $pdo->query("SELECT asset_tag, name, location FROM assets WHERE status != 'disposed' ORDER BY asset_tag ASC")->fetchAll();
} catch (\PDOException $e) {
    $error = "Data fetch failure: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category_id = intval($_POST['category_id']);
    $asset_tag   = !empty($_POST['asset_tag']) ? trim($_POST['asset_tag']) : null;
    $client_id   = $_SESSION['user_id'];
    $priority    = 'low'; 
    $screenshot_path = null;

    // File Upload Processing Logic
    if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES['screenshot']['tmp_name'];
        $file_name = $_FILES['screenshot']['name'];
        $file_size = $_FILES['screenshot']['size'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($file_ext, $allowed_exts)) {
            $error = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
        } elseif ($file_size > 5000000) {
            $error = "File is too large. Maximum size is 5MB.";
        } else {
            $new_file_name = uniqid('err_', true) . '.' . $file_ext;
            $dest_path     = 'uploads/' . $new_file_name;
            if (move_uploaded_file($file_tmp, $dest_path)) {
                $screenshot_path = $dest_path;
            }
        }
    }

    if (empty($error)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO tickets (client_id, category_id, asset_tag, title, description, screenshot_path, priority, status) 
                VALUES (:client_id, :category_id, :asset_tag, :title, :description, :screenshot_path, :priority, 'open')
            ");
            $stmt->execute([
                'client_id'       => $client_id,
                'category_id'     => $category_id,
                'asset_tag'       => $asset_tag,
                'title'           => $title,
                'description'     => $description,
                'screenshot_path' => $screenshot_path,
                'priority'        => $priority
            ]);

            header("Location: dashboard.php?status=ticket_created");
            exit;
        } catch (\PDOException $e) {
            $error = "Submission failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Ticket - Support Desk</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { background: #f3f4f6; display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: #1e3a8a; color: white; padding: 25px; display: flex; flex-direction: column; }
        .sidebar h2 { font-size: 20px; margin-bottom: 30px; text-align: center; border-bottom: 1px solid #3b82f6; padding-bottom: 15px; }
        .sidebar a { color: #d1d5db; text-decoration: none; padding: 12px; display: block; border-radius: 6px; margin-bottom: 10px; font-size: 15px; }
        .sidebar a:hover, .sidebar a.active { background: #2563eb; color: white; }
        .logout-btn { margin-top: auto; background: #dc2626 !important; text-align: center; font-weight: bold; }
        
        .main-content { flex: 1; padding: 40px; display: flex; justify-content: center; }
        .form-container { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); width: 100%; max-width: 600px; height: fit-content; }
        .form-container h1 { color: #111827; margin-bottom: 20px; font-size: 24px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: bold; margin-bottom: 8px; color: #374151; font-size: 14px; }
        input[type="text"], select, textarea { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 15px; background: #fff; }
        textarea { height: 120px; resize: vertical; }
        button { background: #2563eb; color: white; border: none; padding: 12px 20px; font-size: 16px; font-weight: bold; border-radius: 6px; cursor: pointer; width: 100%; }
        button:hover { background: #1d4ed8; }
        .error { color: #dc2626; background: #fee2e2; padding: 10px; border-radius: 4px; margin-bottom: 20px; font-size: 14px; }
        .file-hint { font-size: 12px; color: #6b7280; margin-top: 5px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Support Desk</h2>
        <a href="dashboard.php">🏠 Home Dashboard</a>
        <a href="submit-ticket.php" class="active">📝 Submit a Ticket</a>
        <a href="history.php">📋 My Ticket History</a>
        <a href="actions/logout.php" class="logout-btn">🚪 Sign Out</a>
    </div>

    <div class="main-content">
        <div class="form-container">
            <h1>Create New Support Ticket</h1>
            
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Issue Title</label>
                    <input type="text" name="title" required placeholder="e.g., Computer monitor blank upon startup">
                </div>

                <div class="form-group">
                    <label>IT Category</label>
                    <select name="category_id" required>
                        <option value="">-- Select Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Affected Hardware Equipment (Optional)</label>
                    <select name="asset_tag">
                        <option value="">-- No Specific Hardware Asset (Software/Access Issue) --</option>
                        <?php foreach ($assets as $asset): ?>
                            <option value="<?php echo htmlspecialchars($asset['asset_tag']); ?>">
                                [<?php echo htmlspecialchars($asset['asset_tag']); ?>] <?php echo htmlspecialchars($asset['name']); ?> — Location: <?php echo htmlspecialchars($asset['location']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Detailed Description</label>
                    <textarea name="description" required placeholder="Please state any specific error codes flashing on the hardware..."></textarea>
                </div>

                <div class="form-group">
                    <label>Attach Screenshot (Optional)</label>
                    <input type="file" name="screenshot" accept="image/png, image/jpeg, image/gif">
                    <div class="file-hint">Max file size: 5MB. Accepted formats: JPG, PNG, GIF.</div>
                </div>

                <button type="submit">Submit Ticket</button>
            </form>
        </div>
    </div>

</body>
</html>
EOFcat << 'EOF' > submit-ticket.php
<?php
// submit-ticket.php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'client') {
    header("Location: index.php");
    exit;
}

$error = '';

try {
    // 1. Fetch categories for the dropdown
    $categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
    
    // 2. Fetch active hardware assets for the equipment tracking dropdown
    $assets = $pdo->query("SELECT asset_tag, name, location FROM assets WHERE status != 'disposed' ORDER BY asset_tag ASC")->fetchAll();
} catch (\PDOException $e) {
    $error = "Data fetch failure: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category_id = intval($_POST['category_id']);
    $asset_tag   = !empty($_POST['asset_tag']) ? trim($_POST['asset_tag']) : null;
    $client_id   = $_SESSION['user_id'];
    $priority    = 'low'; 
    $screenshot_path = null;

    // File Upload Processing Logic
    if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES['screenshot']['tmp_name'];
        $file_name = $_FILES['screenshot']['name'];
        $file_size = $_FILES['screenshot']['size'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($file_ext, $allowed_exts)) {
            $error = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
        } elseif ($file_size > 5000000) {
            $error = "File is too large. Maximum size is 5MB.";
        } else {
            $new_file_name = uniqid('err_', true) . '.' . $file_ext;
            $dest_path     = 'uploads/' . $new_file_name;
            if (move_uploaded_file($file_tmp, $dest_path)) {
                $screenshot_path = $dest_path;
            }
        }
    }

    if (empty($error)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO tickets (client_id, category_id, asset_tag, title, description, screenshot_path, priority, status) 
                VALUES (:client_id, :category_id, :asset_tag, :title, :description, :screenshot_path, :priority, 'open')
            ");
            $stmt->execute([
                'client_id'       => $client_id,
                'category_id'     => $category_id,
                'asset_tag'       => $asset_tag,
                'title'           => $title,
                'description'     => $description,
                'screenshot_path' => $screenshot_path,
                'priority'        => $priority
            ]);

            header("Location: dashboard.php?status=ticket_created");
            exit;
        } catch (\PDOException $e) {
            $error = "Submission failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Ticket - Support Desk</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { background: #f3f4f6; display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: #1e3a8a; color: white; padding: 25px; display: flex; flex-direction: column; }
        .sidebar h2 { font-size: 20px; margin-bottom: 30px; text-align: center; border-bottom: 1px solid #3b82f6; padding-bottom: 15px; }
        .sidebar a { color: #d1d5db; text-decoration: none; padding: 12px; display: block; border-radius: 6px; margin-bottom: 10px; font-size: 15px; }
        .sidebar a:hover, .sidebar a.active { background: #2563eb; color: white; }
        .logout-btn { margin-top: auto; background: #dc2626 !important; text-align: center; font-weight: bold; }
        
        .main-content { flex: 1; padding: 40px; display: flex; justify-content: center; }
        .form-container { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); width: 100%; max-width: 600px; height: fit-content; }
        .form-container h1 { color: #111827; margin-bottom: 20px; font-size: 24px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: bold; margin-bottom: 8px; color: #374151; font-size: 14px; }
        input[type="text"], select, textarea { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 15px; background: #fff; }
        textarea { height: 120px; resize: vertical; }
        button { background: #2563eb; color: white; border: none; padding: 12px 20px; font-size: 16px; font-weight: bold; border-radius: 6px; cursor: pointer; width: 100%; }
        button:hover { background: #1d4ed8; }
        .error { color: #dc2626; background: #fee2e2; padding: 10px; border-radius: 4px; margin-bottom: 20px; font-size: 14px; }
        .file-hint { font-size: 12px; color: #6b7280; margin-top: 5px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Support Desk</h2>
        <a href="dashboard.php">🏠 Home Dashboard</a>
        <a href="submit-ticket.php" class="active">📝 Submit a Ticket</a>
        <a href="history.php">📋 My Ticket History</a>
        <a href="actions/logout.php" class="logout-btn">🚪 Sign Out</a>
    </div>

    <div class="main-content">
        <div class="form-container">
            <h1>Create New Support Ticket</h1>
            
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Issue Title</label>
                    <input type="text" name="title" required placeholder="e.g., Computer monitor blank upon startup">
                </div>

                <div class="form-group">
                    <label>IT Category</label>
                    <select name="category_id" required>
                        <option value="">-- Select Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Affected Hardware Equipment (Optional)</label>
                    <select name="asset_tag">
                        <option value="">-- No Specific Hardware Asset (Software/Access Issue) --</option>
                        <?php foreach ($assets as $asset): ?>
                            <option value="<?php echo htmlspecialchars($asset['asset_tag']); ?>">
                                [<?php echo htmlspecialchars($asset['asset_tag']); ?>] <?php echo htmlspecialchars($asset['name']); ?> — Location: <?php echo htmlspecialchars($asset['location']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Detailed Description</label>
                    <textarea name="description" required placeholder="Please state any specific error codes flashing on the hardware..."></textarea>
                </div>

                <div class="form-group">
                    <label>Attach Screenshot (Optional)</label>
                    <input type="file" name="screenshot" accept="image/png, image/jpeg, image/gif">
                    <div class="file-hint">Max file size: 5MB. Accepted formats: JPG, PNG, GIF.</div>
                </div>

                <button type="submit">Submit Ticket</button>
            </form>
        </div>
    </div>

</body>
</html>
