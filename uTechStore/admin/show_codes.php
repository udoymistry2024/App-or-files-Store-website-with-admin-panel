<?php
session_start();

if (!isset($_SESSION['security_codes_to_show'])) {
    header("Location: index.php");
    exit();
}

$codes = $_SESSION['security_codes_to_show'];
unset($_SESSION['security_codes_to_show']); // Unset after showing
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Security Codes</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <h2>IMPORTANT: Save Your Security Codes</h2>
            <p class="warning">
                These codes are required to reset your password if you forget it.
                Store them in a safe place. You will not be shown these again.
            </p>
            <div class="code-list">
                <?php foreach ($codes as $code): ?>
                    <code><?php echo $code; ?></code>
                <?php endforeach; ?>
            </div>
            <a href="index.php" class="button-link">I have saved my codes, proceed to Dashboard</a>
        </div>
    </div>
</body>
</html>