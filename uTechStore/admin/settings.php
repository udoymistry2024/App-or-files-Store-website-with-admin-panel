<?php
require_once 'auth.php';
requireLogin();
if ($_SESSION['admin_role'] !== 'owner') {
    header("Location: index.php"); // Only owner can access
    exit();
}
require_once '../config/db.php';
$message = '';

// Fetch current settings
$settings_result = $conn->query("SELECT * FROM settings");
$settings = [];
while ($row = $settings_result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update site name
    if (isset($_POST['site_name'])) {
        $site_name = $_POST['site_name'];
        $stmt = $conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'site_name'");
        $stmt->bind_param("s", $site_name);
        $stmt->execute();
        $settings['site_name'] = $site_name; // Update local variable
        $message = "Settings updated successfully!";
    }

    // Update logo
    if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['site_logo'];
        $upload_dir = '../assets/images/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $file_name = 'logo_' . time() . '_' . basename($file['name']);
        $target_path = $upload_dir . $file_name;
        $db_path = 'assets/images/' . $file_name;

        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            $stmt = $conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'site_logo'");
            $stmt->bind_param("s", $db_path);
            $stmt->execute();
            $settings['site_logo'] = $db_path; // Update local variable
            $message = "Logo updated successfully!";
        } else {
            $message = "Error uploading logo.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Site Settings</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        <main class="content">
            <h1>Site Settings</h1>
            <?php if ($message) echo "<p class='message'>$message</p>"; ?>

            <div class="card">
                <form method="post" enctype="multipart/form-data">
                    <h2>General Settings</h2>
                    <div class="input-group">
                        <label for="site_name">Site Name</label>
                        <input type="text" name="site_name" value="<?php echo htmlspecialchars($settings['site_name']); ?>">
                    </div>
                    <div class="input-group">
                        <label for="site_logo">Site Logo</label>
                        <input type="file" name="site_logo" accept="image/*">
                        <p>Current Logo: <img src="../<?php echo htmlspecialchars($settings['site_logo']); ?>" alt="logo" style="max-height: 40px; background: #fff; padding: 5px; border-radius: 4px;"></p>
                    </div>
                    <button type="submit">Save Settings</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>