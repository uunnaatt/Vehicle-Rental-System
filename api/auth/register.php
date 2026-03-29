<?php
// api/auth/register.php
include_once '../../config/database.php';
include_once '../../models/User.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->full_name) && !empty($data->phone_or_email) && !empty($data->password)) {
    $user->full_name = $data->full_name;
    $user->phone_or_email = $data->phone_or_email;
    $user->password = $data->password;

    if($user->userExists()) {
        http_response_code(400);
        echo json_encode(array("message" => "User already exists."));
    } else {
        if($user->register()) {
            http_response_code(201);
            echo json_encode(array("message" => "User was successfully registered."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to register user."));
        }
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Incomplete data. Please provide full_name, phone_or_email, and password."));
}
?>
