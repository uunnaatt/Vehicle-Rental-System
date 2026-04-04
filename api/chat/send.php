<?php
// api/chat/send.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../../config/database.php';
include_once '../../models/Message.php';

$database = new Database();
$db = $database->getConnection();
$msg = new Message($db);

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->sender_id) && !empty($data->receiver_id) && !empty($data->message)) {
    $msg->sender_id = $data->sender_id;
    $msg->receiver_id = $data->receiver_id;
    $msg->message = $data->message;
    
    if ($msg->send()) {
        http_response_code(201);
        echo json_encode(["message" => "Sent."]);
    } else {
        http_response_code(503);
        echo json_encode(["message" => "Failed."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Incomplete headers."]);
}
?>
