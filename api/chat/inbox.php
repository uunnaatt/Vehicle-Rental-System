<?php
// api/chat/inbox.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../models/Message.php';

$database = new Database();
$db = $database->getConnection();
$msg = new Message($db);

$stmt = $msg->get_admin_inbox();
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