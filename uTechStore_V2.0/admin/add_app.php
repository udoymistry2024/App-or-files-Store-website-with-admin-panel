<?php
require_once 'auth.php';
requireLogin();
require_once '../config/db.php';
$edit_app = null;

if (isset($_GET['edit'])) {
    $id_to_edit = (int)$_GET['edit'];
    $result = $conn->query("SELECT * FROM apps WHERE id = $id_to_edit");
    if ($result->num_rows === 1) {
        $edit_app = $result->fetch_assoc();
    }
}

$categories_result = $conn->query("SELECT * FROM categories ORDER BY name ASC");

// FTP ফোল্ডার থেকে ফাইল স্ক্যান করা
$ftp_dir = '../uploads/files/ftp/';
if (!is_dir($ftp_dir)) mkdir($ftp_dir, 0755, true);
$ftp_files = array_diff(scandir($ftp_dir), array('.', '..'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title><?php echo $edit_app ? 'Edit App' : 'Add New App'; ?></title><link rel="stylesheet" href="admin_style.css">
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar.php'; ?>
    <main class="content">
        <h1><?php echo $edit_app ? 'Edit App' : 'Add New App'; ?></h1>
        <div id="responseMessage"></div>
        <div class="card">
            <form id="appForm" enctype="multipart/form-data">
                <input type="hidden" name="app_id" value="<?php echo $edit_app['id'] ?? 0; ?>">
                <div class="input-group"><label>Title</label><input type="text" name="title" value="<?php echo htmlspecialchars($edit_app['title'] ?? ''); ?>" required></div>
                <div class="input-group"><label>Category</label><select name="category_id" required><?php mysqli_data_seek($categories_result, 0); while ($cat = $categories_result->fetch_assoc()){ echo "<option value='{$cat['id']}' ".((isset($edit_app) && $edit_app['category_id'] == $cat['id']) ? 'selected' : '').">".htmlspecialchars($cat['name'])."</option>"; } ?></select></div>
                <div class="input-group"><label>Description</label><textarea name="description" rows="4" required><?php echo htmlspecialchars($edit_app['description'] ?? ''); ?></textarea></div>
                <div class="input-group"><label>Image 1</label><input type="file" name="image1"><?php if (!empty($edit_app['image1'])){ echo "<p>Current: ".basename($edit_app['image1'])."</p>"; } ?></div>
                <div class="input-group"><label>Image 2</label><input type="file" name="image2"><?php if (!empty($edit_app['image2'])){ echo "<p>Current: ".basename($edit_app['image2'])."</p>"; } ?></div>
                <div class="input-group"><label>Image 3</label><input type="file" name="image3"><?php if (!empty($edit_app['image3'])){ echo "<p>Current: ".basename($edit_app['image3'])."</p>"; } ?></div>
                <div class="input-group"><label>YouTube Link</label><input type="url" name="youtube_link" value="<?php echo htmlspecialchars($edit_app['youtube_link'] ?? ''); ?>"></div>

                <!-- নতুন আপলোড মেথড অপশন -->
                <div class="input-group">
                    <label>App/Game File Upload Method</label>
                    <div>
                        <label><input type="radio" name="upload_method" value="direct" checked> Direct Upload (for small files)</label>
                        <label style="margin-left: 20px;"><input type="radio" name="upload_method" value="ftp"> Select from FTP (for large files)</label>
                    </div>
                </div>

                <!-- ডাইরেক্ট আপলোড ফিল্ড -->
                <div class="input-group" id="directUploadContainer">
                    <label>Upload File Directly</label>
                    <input type="file" name="app_file" id="app_file_input">
                    <?php if (!empty($edit_app['file_path'])){ echo "<p>Current: ".basename($edit_app['file_path'])."</p>"; } ?>
                </div>

                <!-- FTP সিলেক্ট ফিল্ড -->
                <div class="input-group" id="ftpUploadContainer" style="display: none;">
                    <label>Select File from FTP</label>
                    <p class="info-text">Upload large files to the `uploads/files/ftp/` directory using an FTP client like FileZilla.</p>
                    <select name="ftp_file_select">
                        <option value="">-- Select a file --</option>
                        <?php foreach($ftp_files as $file): ?>
                            <option value="<?php echo htmlspecialchars($file); ?>"><?php echo htmlspecialchars($file); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="progress-bar-container" style="display: none;"><div class="progress-bar"><div class="progress-bar-fill"></div></div><div class="progress-bar-text">0%</div></div>
                <button type="submit" name="save_app">Save App</button>
            </form>
        </div>
    </main>
</div>

<script>
    document.querySelectorAll('input[name="upload_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'direct') {
                document.getElementById('directUploadContainer').style.display = 'block';
                document.getElementById('ftpUploadContainer').style.display = 'none';
            } else {
                document.getElementById('directUploadContainer').style.display = 'none';
                document.getElementById('ftpUploadContainer').style.display = 'block';
            }
        });
    });

    document.getElementById('appForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const xhr = new XMLHttpRequest();
        const progressBarContainer = document.querySelector('.progress-bar-container');
        const progressBarFill = document.querySelector('.progress-bar-fill');
        const progressBarText = document.querySelector('.progress-bar-text');
        const responseMessage = document.getElementById('responseMessage');
        if (document.getElementById('app_file_input').files.length > 0 && formData.get('upload_method') === 'direct') {
            progressBarContainer.style.display = 'block';
        }
        responseMessage.innerHTML = '';
        xhr.open('POST', 'upload_handler.php', true);
        xhr.upload.onprogress = function(event) {
            if (event.lengthComputable) {
                const percentComplete = Math.round((event.loaded / event.total) * 100);
                progressBarFill.style.width = percentComplete + '%';
                progressBarText.textContent = percentComplete + '%';
            }
        };
        xhr.onload = function() {
            progressBarContainer.style.display = 'none';
            progressBarFill.style.width = '0%';
            progressBarText.textContent = '0%';
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    responseMessage.className = response.success ? 'message' : 'error';
                    responseMessage.textContent = response.message;
                    if (response.success && !formData.get('app_id') > 0) {
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