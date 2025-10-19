<?php
require_once 'auth.php';
requireLogin();
require_once '../config/db.php';

header('Content-Type: application/json');

function handleFileUpload($file, $uploadDir) {
    if ($file['error'] !== UPLOAD_ERR_OK || empty($file['name'])) return [null, null];
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $fileName = uniqid() . '-' . basename($file['name']);
    $targetPath = $uploadDir . $fileName;
    $dbPath = str_replace('../', '', $targetPath);
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return [$targetPath, $dbPath];
    }
    return [null, null];
}

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    
    list(, $image1_path) = isset($_FILES['image1']) ? handleFileUpload($_FILES['image1'], '../uploads/images/') : [null, null];
    list(, $image2_path) = isset($_FILES['image2']) ? handleFileUpload($_FILES['image2'], '../uploads/images/') : [null, null];
    list(, $image3_path) = isset($_FILES['image3']) ? handleFileUpload($_FILES['image3'], '../uploads/images/') : [null, null];
    list(, $file_path) = isset($_FILES['app_file']) ? handleFileUpload($_FILES['app_file'], '../uploads/files/') : [null, null];
    
    $image1_path = $image1_path ?? $existing_paths['image1'];
    $image2_path = $image2_path ?? $existing_paths['image2'];
    $image3_path = $image3_path ?? $existing_paths['image3'];
    $file_path = $file_path ?? $existing_paths['file_path'];

    if ($is_edit) {
        $stmt = $conn->prepare("UPDATE apps SET title=?, category_id=?, description=?, image1=?, image2=?, image3=?, youtube_link=?, file_path=? WHERE id=?");
        $stmt->bind_param("sissssssi", $title, $category_id, $description, $image1_path, $image2_path, $image3_path, $youtube_link, $file_path, $app_id);
    } else {
        if (empty($file_path)) {
            $response = ['success' => false, 'message' => 'App file is required for new uploads.'];
            echo json_encode($response);
            exit();
        }
        $stmt = $conn->prepare("INSERT INTO apps (title, category_id, description, image1, image2, image3, youtube_link, file_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sissssss", $title, $category_id, $description, $image1_path, $image2_path, $image3_path, $youtube_link, $file_path);
    }

    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => 'App ' . ($is_edit ? 'updated' : 'added') . ' successfully!'];
    } else {
        $response = ['success' => false, 'message' => 'Database error: ' . $stmt->error];
    }
    $stmt->close();
}

echo json_encode($response);
?>