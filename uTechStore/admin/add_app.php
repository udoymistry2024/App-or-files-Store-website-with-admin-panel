<?php
require_once 'auth.php';
requireLogin();
require_once '../config/db.php';
$message = '';
$edit_app = null;

// File upload helper function
function handleFileUpload($file, $uploadDir) {
    if ($file['error'] !== UPLOAD_ERR_OK || empty($file['name'])) return [null, null];
    // Ensure the upload directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $fileName = uniqid() . '-' . basename($file['name']);
    $targetPath = $uploadDir . $fileName;
    $dbPath = str_replace('../', '', $targetPath);
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return [$targetPath, $dbPath];
    }
    return [null, null];
}

// Handle Add/Edit App Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_app'])) {
    $app_id = (int)$_POST['app_id'];
    $title = $_POST['title'];
    $category_id = $_POST['category_id'];
    $description = $_POST['description'];
    $youtube_link = $_POST['youtube_link'];
    
    $is_edit = $app_id > 0;

    $existing_paths = ['image1' => null, 'image2' => null, 'image3' => null, 'file_path' => null];
    if ($is_edit) {
        $res = $conn->query("SELECT image1, image2, image3, file_path FROM apps WHERE id = $app_id");
        if ($res->num_rows > 0) $existing_paths = $res->fetch_assoc();
    }
    
    list(, $image1_path) = handleFileUpload($_FILES['image1'], '../uploads/images/');
    list(, $image2_path) = handleFileUpload($_FILES['image2'], '../uploads/images/');
    list(, $image3_path) = handleFileUpload($_FILES['image3'], '../uploads/images/');
    list(, $file_path) = handleFileUpload($_FILES['app_file'], '../uploads/files/');
    
    $image1_path = $image1_path ?? $existing_paths['image1'];
    $image2_path = $image2_path ?? $existing_paths['image2'];
    $image3_path = $image3_path ?? $existing_paths['image3'];
    $file_path = $file_path ?? $existing_paths['file_path'];

    if ($is_edit) {
        $stmt = $conn->prepare("UPDATE apps SET title=?, category_id=?, description=?, image1=?, image2=?, image3=?, youtube_link=?, file_path=? WHERE id=?");
        $stmt->bind_param("sissssssi", $title, $category_id, $description, $image1_path, $image2_path, $image3_path, $youtube_link, $file_path, $app_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO apps (title, category_id, description, image1, image2, image3, youtube_link, file_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sissssss", $title, $category_id, $description, $image1_path, $image2_path, $image3_path, $youtube_link, $file_path);
    }

    if ($stmt->execute()) {
        $message = "App " . ($is_edit ? "updated" : "added") . " successfully!";
    } else {
        $message = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Handle Edit Request (to pre-fill the form)
if (isset($_GET['edit'])) {
    $id_to_edit = (int)$_GET['edit'];
    $result = $conn->query("SELECT * FROM apps WHERE id = $id_to_edit");
    if ($result->num_rows === 1) {
        $edit_app = $result->fetch_assoc();
    }
}

$categories_result = $conn->query("SELECT * FROM categories ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $edit_app ? 'Edit App' : 'Add New App'; ?></title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar.php'; ?>
    <main class="content">
        <h1><?php echo $edit_app ? 'Edit App' : 'Add New App'; ?></h1>
        <?php if ($message) echo "<p class='message'>$message</p>"; ?>

        <div class="card">
            <form action="add_app.php<?php echo $edit_app ? '?edit=' . $edit_app['id'] : ''; ?>" method="post" enctype="multipart/form-data" class="app-form">
                <input type="hidden" name="app_id" value="<?php echo $edit_app['id'] ?? 0; ?>">
                
                <div class="input-group">
                    <label>Title</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($edit_app['title'] ?? ''); ?>" required>
                </div>
                
                <div class="input-group">
                    <label>Category</label>
                    <select name="category_id" required>
                        <?php while ($cat = $categories_result->fetch_assoc()): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php if (isset($edit_app) && $edit_app['category_id'] == $cat['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="input-group">
                    <label>Description</label>
                    <textarea name="description" rows="4" required><?php echo htmlspecialchars($edit_app['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="input-group"><label>Image 1</label><input type="file" name="image1"><?php if (!empty($edit_app['image1'])): ?><p>Current: <?php echo basename($edit_app['image1']); ?></p><?php endif; ?></div>
                <div class="input-group"><label>Image 2</label><input type="file" name="image2"><?php if (!empty($edit_app['image2'])): ?><p>Current: <?php echo basename($edit_app['image2']); ?></p><?php endif; ?></div>
                <div class="input-group"><label>Image 3</label><input type="file" name="image3"><?php if (!empty($edit_app['image3'])): ?><p>Current: <?php echo basename($edit_app['image3']); ?></p><?php endif; ?></div>

                <div class="input-group">
                    <label>YouTube Link</label>
                    <input type="url" name="youtube_link" value="<?php echo htmlspecialchars($edit_app['youtube_link'] ?? ''); ?>">
                </div>
                
                <div class="input-group">
                    <label>App/Game File <?php if(!$edit_app) echo '(Required)'; ?></label>
                    <input type="file" name="app_file" <?php if(!$edit_app) echo 'required'; ?>>
                    <?php if (!empty($edit_app['file_path'])): ?><p>Current: <?php echo basename($edit_app['file_path']); ?></p><?php endif; ?>
                </div>

                <button type="submit" name="save_app">Save App</button>
            </form>
        </div>
    </main>
</div>
</body>
</html>