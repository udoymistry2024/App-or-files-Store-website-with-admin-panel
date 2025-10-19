<?php
require_once 'auth.php';
requireLogin();
if ($_SESSION['admin_role'] !== 'owner') {
    header("Location: index.php"); // Only owner can access
    exit();
}
require_once '../config/db.php';
$message = '';
$new_code = '';

// Generate invitation code
if (isset($_POST['generate_code'])) {
    $code = bin2hex(random_bytes(8)); // Generate a random 16-char code
    $stmt = $conn->prepare("INSERT INTO invitation_codes (code) VALUES (?)");
    $stmt->bind_param("s", $code);
    if ($stmt->execute()) {
        $message = "New invitation code generated successfully!";
        $new_code = $code;
    } else {
        $message = "Error generating code.";
    }
}

// Delete user
if (isset($_GET['delete'])) {
    $id_to_delete = intval($_GET['delete']);
    // You cannot delete yourself
    if ($id_to_delete !== $_SESSION['admin_id']) {
        $stmt = $conn->prepare("DELETE FROM admins WHERE id = ? AND role = 'admin'");
        $stmt->bind_param("i", $id_to_delete);
        if ($stmt->execute()) {
            $message = "Admin user deleted successfully.";
        } else {
            $message = "Error deleting user.";
        }
    }
}

$admins_result = $conn->query("SELECT id, username, role FROM admins");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        <main class="content">
            <h1>Manage Users</h1>
            <?php if ($message) echo "<p class='message'>$message</p>"; ?>

            <div class="card">
                <h2>Generate Invitation Code</h2>
                <form method="post">
                    <button type="submit" name="generate_code">Generate New Code</button>
                </form>
                <?php if ($new_code): ?>
                <div class="new-code-info">
                    <p>Share this link with the new admin:</p>
                    <input type="text" value="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/signup.php?invite=' . $new_code; ?>" readonly>
                </div>
                <?php endif; ?>
            </div>

            <div class="table-container card">
                <h2>All Admin Users</h2>
                <table>
                    <thead><tr><th>Username</th><th>Role</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php while ($admin = $admins_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($admin['username']); ?></td>
                            <td><?php echo htmlspecialchars($admin['role']); ?></td>
                            <td class="actions">
                                <?php if ($admin['role'] === 'admin'): ?>
                                <a href="manage_users.php?delete=<?php echo $admin['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                                <?php else: echo 'N/A'; endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>