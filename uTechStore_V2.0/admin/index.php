<?php
require_once '../config/db.php';
require_once 'auth.php';

// Check if any admin exists. If not, redirect to signup.
$result = $conn->query("SELECT id FROM admins LIMIT 1");
if ($result->num_rows === 0) {
    header('Location: signup.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password, role FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $admin_result = $stmt->get_result();
    
    if ($admin_result->num_rows === 1) {
        $admin = $admin_result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_role'] = $admin['role'];
            header("Location: index.php");
            exit();
        }
    }
    $error = "Invalid username or password!";
    $stmt->close();
}

if (!isLoggedIn()) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="login-container">
        <form method="POST" class="login-form">
            <h2>Admin Login</h2>
            <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit" name="login">Login</button>
            <a href="forgot_password.php" class="back-link">Forgot Password? (Owner only)</a>
        </form>
    </div>
</body>
</html>
<?php
    exit();
}

// If logged in, show dashboard
$total_apps = $conn->query("SELECT COUNT(*) as count FROM apps")->fetch_assoc()['count'];
$total_categories = $conn->query("SELECT COUNT(*) as count FROM categories")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        <main class="content">
            <h1>Welcome to the Dashboard</h1>
            <div class="dashboard-stats">
                <div class="stat-card">
                    <h2><?php echo $total_apps; ?></h2>
                    <p>Total Apps</p>
                </div>
                <div class="stat-card">
                    <h2><?php echo $total_categories; ?></h2>
                    <p>Total Categories</p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>