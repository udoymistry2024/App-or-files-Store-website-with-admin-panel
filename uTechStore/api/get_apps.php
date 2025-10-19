<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'getApps') {
    $sql = "SELECT * FROM apps ORDER BY upload_date DESC";
    $result = $conn->query($sql);
    $apps = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $apps[] = $row;
        }
    }
    echo json_encode($apps);
} elseif ($action == 'getCategories') {
    $sql = "SELECT * FROM categories ORDER BY name ASC";
    $result = $conn->query($sql);
    $categories = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    echo json_encode($categories);
} else {
    echo json_encode(['error' => 'Invalid action']);
}

$conn->close();
?>