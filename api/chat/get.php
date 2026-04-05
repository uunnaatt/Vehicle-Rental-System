<?php
// api/chat/get.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../models/Message.php';

$database = new Database();
$db = $database->getConnection();
$msg = new Message($db);

$u1 = isset($_GET['user1']) ? $_GET['user1'] : die();
$u2 = isset($_GET['user2']) ? $_GET['user2'] : die(); // Often Admin is user 1 usually

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