<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo "Unauthorized";
    exit;
}

if (!isset($_GET['file'])) {
    http_response_code(400);
    echo "Missing file parameter";
    exit;
}

// Safely resolve the file path to prevent directory traversal
$file = basename($_GET['file']);
$filepath = '../../assets/images/collaterals/' . $file;

if (file_exists($filepath)) {
    $mimeType = mime_content_type($filepath);
    
    // Validate that it is actually an image
    if (strpos($mimeType, 'image/') === 0) {
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    } else {
        http_response_code(403);
        echo "Invalid file type";
    }
} else {
    http_response_code(404);
    echo "File not found";
}
?>
