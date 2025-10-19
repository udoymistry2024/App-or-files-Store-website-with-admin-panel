<?php
require_once 'auth.php';
requireLogin();
require_once '../config/db.php';
$message = '';

// Handle Delete App - এখানে ডিলিট করার কোডটি ঠিক করা হয়েছে
if (isset($_GET['delete'])) {
    $id_to_delete = (int)$_GET['delete'];
    
    // Step 1: ডাটাবেস থেকে ফাইলগুলোর পাথ খুঁজে বের করা
    $stmt = $conn->prepare("SELECT image1, image2, image3, file_path FROM apps WHERE id = ?");
    $stmt->bind_param("i", $id_to_delete);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $paths = $result->fetch_assoc();
        // Step 2: সার্ভার থেকে ফাইলগুলো ডিলিট করা
        foreach ($paths as $path) {
            if (!empty($path) && file_exists('../' . $path)) {
                @unlink('../' . $path);
            }
        }
        $stmt->close();

        // Step 3: ডাটাবেস থেকে অ্যাপের রেকর্ড ডিলিট করা
        $delete_stmt = $conn->prepare("DELETE FROM apps WHERE id = ?");
        $delete_stmt->bind_param("i", $id_to_delete);
        if ($delete_stmt->execute()) {
            $message = "App and all associated files deleted successfully!";
        } else {
            $message = "Error deleting app record from database.";
        }
        $delete_stmt->close();
    } else {
        $message = "App not found.";
    }
}

// ডাটাবেস থেকে সকল অ্যাপের তালিকা নিয়ে আসা
$apps_result = $conn->query("SELECT apps.*, categories.name as category_name FROM apps JOIN categories ON apps.category_id = categories.id ORDER BY apps.id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Apps</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar.php'; ?>
    <main class="content">
        <h1>Manage Uploaded Apps</h1>
        <?php if ($message) echo "<p class='message'>$message</p>"; ?>

        <div class="table-container card">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($apps_result->num_rows > 0): ?>
                        <?php while ($app = $apps_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($app['title']); ?></td>
                            <td><?php echo htmlspecialchars($app['category_name']); ?></td>
                            <td class="actions">
                                <!-- এডিট বাটনটি এখন add_app.php তে পয়েন্ট করবে -->
                                <a href="add_app.php?edit=<?php echo $app['id']; ?>">Edit</a>
                                <a href="manage_apps.php?delete=<?php echo $app['id']; ?>" onclick="return confirm('Are you sure you want to delete this app permanently? This action cannot be undone.')">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">No apps have been uploaded yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>