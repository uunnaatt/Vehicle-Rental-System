<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");

session_unset();
session_regenerate_id(true); // Prevent session fixation
session_destroy();
http_response_code(200);
echo json_encode(["message" => "Logged out securely."]);
?>
