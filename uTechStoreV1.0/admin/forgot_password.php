<?php
require_once '../config/db.php';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $security_code = $_POST['security_code'];
    $new_password = $_POST['new_password'];

    if (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters long.";
    } else {
        // Find admin by username
        $stmt = $conn->prepare("SELECT id, role FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            if ($admin['role'] !== 'owner') {
                $error = "Only the owner can reset password using security codes. Please contact the owner.";
            } else {
                // Check if the security code is valid for this owner
                $code_stmt = $conn->prepare("SELECT id FROM security_codes WHERE admin_id = ? AND code = ?");
                $code_stmt->bind_param("is", $admin['id'], $security_code);
                $code_stmt->execute();
                if ($code_stmt->get_result()->num_rows === 1) {
                    // Code is valid, update password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_stmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
                    $update_stmt->bind_param("si", $hashed_password, $admin['id']);
                    if ($update_stmt->execute()) {
                        $success = "Password has been reset successfully. You can now <a href='index.php'>login</a>.";
                    } else {
                        $error = "Failed to update password. Please try again.";
                    }
                    $update_stmt->close();
                } else {
                    $error = "Invalid security code.";
                }
                $code_stmt->close();
            }
        } else {
            $error = "Invalid username.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="login-container">
        <form method="POST" class="login-form">
            <h2>Reset Password</h2>
            <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>
            <?php if ($success): ?><p class="message"><?php echo $success; ?></p><?php endif; ?>

            <?php if (!$success): ?>
                <p>Only the 'owner' account password can be reset here.</p>
                <div class="input-group">
                    <label for="username">Owner Username</label>
                    <input type="text" name="username" required>
                </div>
                <div class="input-group">
                    <label for="security_code">One of Your 5-Digit Security Codes</label>
                    <input type="text" name="security_code" required>
                </div>
                <div class="input-group">
                    <label for="new_password">New Password</label>
                    <input type="password" name="new_password" required>
                </div>
                <button type="submit">Reset Password</button>
                <a href="index.php" class="back-link">Back to Login</a>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>