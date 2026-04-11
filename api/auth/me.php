<?php
session_start();
session_write_close(); // Release session lock immediately — this is read-only
header("Content-Type: application/json; charset=UTF-8");

if (isset($_SESSION['user_id'])) {
    http_response_code(200);
    echo json_encode([
        "authenticated" => true,
        "user_id" => $_SESSION['user_id'],
        "role" => $_SESSION['role'],
        "full_name" => $_SESSION['full_name'] ?? ''
    ]);
} else {
    http_response_code(401);
    echo json_encode([
        "authenticated" => false,
        "message" => "No active session."
    ]);
}
?>
