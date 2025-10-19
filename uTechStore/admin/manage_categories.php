<?php
require_once 'auth.php';
requireLogin();
require_once '../config/db.php';

$message = '';

// Add category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = $_POST['category_name'];
    $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
    $stmt->bind_param("s", $name);
    if ($stmt->execute()) {
        $message = "Category added successfully!";
    } else {
        $message = "Error adding category.";
    }
    $stmt->close();
}

// Delete category
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Category deleted successfully!";
    } else {
        $message = "Error deleting category. Make sure no apps are using it.";
    }
    $stmt->close();
}

$categories_result = $conn->query("SELECT * FROM categories ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <h3>Admin Panel</h3>
            <nav>
                <a href="index.php">Dashboard</a>
                <a href="manage_apps.php">Manage Apps</a>
                <a href="manage_categories.php" class="active">Manage Categories</a>
                <a href="logout.php">Logout</a>
            </nav>
        </aside>
        <main class="content">
            <h1>Manage Categories</h1>

            <?php if ($message) echo "<p class='message'>$message</p>"; ?>

            <form action="manage_categories.php" method="post" class="category-form">
                <h2>Add New Category</h2>
                <div class="input-group">
                    <label for="category_name">Category Name</label>
                    <input type="text" name="category_name" required>
                </div>
                <button type="submit" name="add_category">Add Category</button>
            </form>

            <div class="table-container">
                <h2>Existing Categories</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($cat = $categories_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cat['name']); ?></td>
                            <td class="actions">
                                <a href="manage_categories.php?delete=<?php echo $cat['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
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