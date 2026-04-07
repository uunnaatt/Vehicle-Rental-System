<?php
// api/chat/get.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../models/Message.php';

$database = new Database();
$db = $database->getConnection();
$msg = new Message($db);

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit;
}

$u1 = $_SESSION['user_id'];
$u2 = isset($_GET['user2']) ? $_GET['user2'] : die(); // The other user in the conversation

$stmt = $msg->get_chat($u1, $u2);
$num = $stmt->rowCount();

$arr = array();
$arr["records"] = array();
if ($num > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        array_push($arr["records"], $row);
    }
}
http_response_code(200);
echo json_encode($arr);
?>