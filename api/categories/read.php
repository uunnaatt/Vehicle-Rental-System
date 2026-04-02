<?php
// api/categories/read.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../models/Category.php';

$database = new Database();
$db = $database->getConnection();

$category = new Category($db);
$stmt = $category->read();
$num = $stmt->rowCount();

if($num > 0) {
    $cat_arr = array();
    $cat_arr["records"] = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        array_push($cat_arr["records"], $row);
    }
    http_response_code(200);
    echo json_encode($cat_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No categories found."));
}
?>
