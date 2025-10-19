<?php
require_once 'auth.php';
requireLogin();
require_once '../config/db.php';
$edit_app = null;

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
        
        <!-- Response message will be shown here by JS -->
        <div id="responseMessage"></div>

        <div class="card">
            <form id="appForm" enctype="multipart/form-data">
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
                    <input type="file" name="app_file" id="app_file_input" <?php if(!$edit_app) echo 'required'; ?>>
                     <?php if (!empty($edit_app['file_path'])): ?><p>Current: <?php echo basename($edit_app['file_path']); ?></p><?php endif; ?>
                </div>

                <!-- Progress Bar HTML -->
                <div class="progress-bar-container" style="display: none;">
                    <div class="progress-bar">
                        <div class="progress-bar-fill"></div>
                    </div>
                    <div class="progress-bar-text">0%</div>
                </div>

                <button type="submit" name="save_app">Save App</button>
            </form>
        </div>
    </main>
</div>

<script>
document.getElementById('appForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const xhr = new XMLHttpRequest();

    const progressBarContainer = document.querySelector('.progress-bar-container');
    const progressBarFill = document.querySelector('.progress-bar-fill');
    const progressBarText = document.querySelector('.progress-bar-text');
    const responseMessage = document.getElementById('responseMessage');

    // Show progress bar only if a new app file is selected
    if (document.getElementById('app_file_input').files.length > 0) {
        progressBarContainer.style.display = 'block';
    }
    responseMessage.innerHTML = ''; // Clear previous messages

    xhr.open('POST', 'upload_handler.php', true);

    xhr.upload.onprogress = function(event) {
        if (event.lengthComputable) {
            const percentComplete = Math.round((event.loaded / event.total) * 100);
            progressBarFill.style.width = percentComplete + '%';
            progressBarText.textContent = percentComplete + '%';
        }
    };

    xhr.onload = function() {
        progressBarContainer.style.display = 'none'; // Hide progress bar on completion
        progressBarFill.style.width = '0%'; // Reset for next time
        progressBarText.textContent = '0%';
        
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                responseMessage.className = response.success ? 'message' : 'error';
                responseMessage.textContent = response.message;

                if (response.success && !formData.get('app_id') > 0) { // If it was a new app, reset form
                    form.reset();
                }
            } catch (err) {
                responseMessage.className = 'error';
                responseMessage.textContent = 'An unexpected error occurred. Invalid server response.';
            }
        } else {
            responseMessage.className = 'error';
            responseMessage.textContent = 'Error: ' + xhr.status;
        }
    };

    xhr.onerror = function() {
        responseMessage.className = 'error';
        responseMessage.textContent = 'Request failed. Check your network connection.';
    };

    xhr.send(formData);
});
</script>

</body>
</html>