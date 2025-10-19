<?php
require_once '../config/db.php';

// Check if any admin exists. If so, check for invitation code.
$result = $conn->query("SELECT id FROM admins");
$is_first_admin = ($result->num_rows === 0);
$invitation_code = isset($_GET['invite']) ? $_GET['invite'] : '';
$error = '';
$valid_invite = false;

if (!$is_first_admin) {
    if (!empty($invitation_code)) {
        $stmt = $conn->prepare("SELECT is_used FROM invitation_codes WHERE code = ?");
        $stmt->bind_param("s", $invitation_code);
        $stmt->execute();
        $invite_result = $stmt->get_result();
        if ($invite_result->num_rows > 0) {
            $invite_data = $invite_result->fetch_assoc();
            if (!$invite_data['is_used']) {
                $valid_invite = true;
            } else {
                $error = "This invitation code has already been used.";
            }
        } else {
            $error = "Invalid invitation code.";
        }
        $stmt->close();
    } else {
        // If not the first admin and no invite code, redirect.
        header("Location: index.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Username and password cannot be empty.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Check if username already exists
        $stmt = $conn->prepare("SELECT id FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = "Username already taken. Please choose another one.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = $is_first_admin ? 'owner' : 'admin';

            $stmt = $conn->prepare("INSERT INTO admins (username, password, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $hashed_password, $role);
            if ($stmt->execute()) {
                $admin_id = $stmt->insert_id;
                
                // If this was an invited user, mark the code as used
                if (!$is_first_admin && $valid_invite) {
                    $update_stmt = $conn->prepare("UPDATE invitation_codes SET is_used = 1 WHERE code = ?");
                    $update_stmt->bind_param("s", $invitation_code);
                    $update_stmt->execute();
                    $update_stmt->close();
                }

                // Generate and store security codes for the owner
                if ($is_first_admin) {
                    $security_codes = [];
                    $code_stmt = $conn->prepare("INSERT INTO security_codes (admin_id, code) VALUES (?, ?)");
                    for ($i = 0; $i < 5; $i++) {
                        $code = rand(10000, 99999);
                        $security_codes[] = $code;
                        $code_stmt->bind_param("is", $admin_id, $code);
                        $code_stmt->execute();
                    }
                    $_SESSION['security_codes_to_show'] = $security_codes;
                    header("Location: show_codes.php");
                    exit();
                } else {
                    // For regular admins, just log them in
                    $_SESSION['admin_id'] = $admin_id;
                    $_SESSION['admin_role'] = $role;
                    header("Location: index.php");
                    exit();
                }
            } else {
                $error = "Registration failed. Please try again.";
            }
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
    <title>Admin Sign Up</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="login-container">
        <form method="POST" class="login-form">
            <h2><?php echo $is_first_admin ? 'Create Owner Account' : 'Create Admin Account'; ?></h2>
            <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>

            <?php if ($is_first_admin || $valid_invite): ?>
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" required>
                </div>
                <div class="input-group">
                    <label for="password">Password (min. 6 characters)</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <button type="submit">Sign Up</button>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>