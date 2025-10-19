<?php
require_once 'auth.php';
requireLogin();
if ($_SESSION['admin_role'] !== 'owner') {
    header("Location: index.php"); // Only owner can access
    exit();
}
require_once '../config/db.php';
$message = '';

// Handle General Footer & WhatsApp Settings Update
if (isset($_POST['update_general'])) {
    $copyright_text = $_POST['copyright_text'];
    $whatsapp_number = $_POST['whatsapp_number'];
    $whatsapp_enabled = isset($_POST['whatsapp_enabled']) ? '1' : '0';

    $conn->query("UPDATE settings SET setting_value = '$copyright_text' WHERE setting_key = 'copyright_text'");
    $conn->query("UPDATE settings SET setting_value = '$whatsapp_number' WHERE setting_key = 'whatsapp_number'");
    $conn->query("UPDATE settings SET setting_value = '$whatsapp_enabled' WHERE setting_key = 'whatsapp_enabled'");
    $message = "Settings updated successfully!";
}

// Handle Add New Contact Item
if (isset($_POST['add_contact'])) {
    $icon = $_POST['icon_class'];
    $link = $_POST['link_url'];
    $text = $_POST['display_text'];
    $stmt = $conn->prepare("INSERT INTO contact_details (icon_class, link_url, display_text) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $icon, $link, $text);
    $stmt->execute();
    $message = "Contact item added successfully!";
}

// Handle Delete Contact Item
if (isset($_GET['delete_contact'])) {
    $id_to_delete = (int)$_GET['delete_contact'];
    $stmt = $conn->prepare("DELETE FROM contact_details WHERE id = ?");
    $stmt->bind_param("i", $id_to_delete);
    $stmt->execute();
    $message = "Contact item deleted successfully!";
}


// Fetch current settings and contacts
$settings_result = $conn->query("SELECT * FROM settings");
$settings = [];
while ($row = $settings_result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$contacts_result = $conn->query("SELECT * FROM contact_details");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Footer & Contact Settings</title>
    <link rel="stylesheet" href="admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar.php'; ?>
    <main class="content">
        <h1>Footer & Contact Settings</h1>
        <?php if ($message) echo "<p class='message'>$message</p>"; ?>

        <div class="card">
            <form method="post">
                <h2>General Settings</h2>
                <div class="input-group">
                    <label for="copyright_text">Copyright Text</label>
                    <input type="text" name="copyright_text" value="<?php echo htmlspecialchars($settings['copyright_text']); ?>">
                </div>
                <div class="input-group">
                    <label for="whatsapp_number">WhatsApp Number (with country code, e.g., 12345678901)</label>
                    <input type="text" name="whatsapp_number" value="<?php echo htmlspecialchars($settings['whatsapp_number']); ?>">
                </div>
                <div class="input-group">
                    <label><input type="checkbox" name="whatsapp_enabled" value="1" <?php echo ($settings['whatsapp_enabled'] == '1') ? 'checked' : ''; ?>> Enable Floating WhatsApp Button</label>
                </div>
                <button type="submit" name="update_general">Save General Settings</button>
            </form>
        </div>

        <div class="card">
            <h2>Manage Contact Details (Footer Links)</h2>
            <form method="post">
                <h3>Add New Contact</h3>
                <div class="input-group">
                    <label>Icon Class (e.g., `fas fa-phone` or `fab fa-facebook`)</label>
                    <input type="text" name="icon_class" placeholder="fas fa-envelope" required>
                </div>
                 <div class="input-group">
                    <label>Link URL (e.g., `mailto:info@example.com` or `tel:+12345`)</label>
                    <input type="text" name="link_url" placeholder="mailto:info@example.com" required>
                </div>
                 <div class="input-group">
                    <label>Display Text (e.g., `info@example.com`)</label>
                    <input type="text" name="display_text" placeholder="info@example.com" required>
                </div>
                <button type="submit" name="add_contact">Add Contact Item</button>
            </form>

            <div class="table-container" style="margin-top: 20px;">
                <h3>Existing Contacts</h3>
                <table>
                    <thead><tr><th>Icon</th><th>Display Text</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php while ($contact = $contacts_result->fetch_assoc()): ?>
                        <tr>
                            <td><i class="<?php echo htmlspecialchars($contact['icon_class']); ?>"></i></td>
                            <td><?php echo htmlspecialchars($contact['display_text']); ?></td>
                            <td class="actions"><a href="footer_settings.php?delete_contact=<?php echo $contact['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>