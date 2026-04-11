<?php
session_start();

// Combined headers from both branches
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

session_unset();

// Kept the security improvement from UnnatBackend
session_regenerate_id(true); 

session_destroy();

http_response_code(200);
echo json_encode(["message" => "Logged out securely."]);
?>