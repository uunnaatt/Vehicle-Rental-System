<?php
// api/auth/login.php
include_once '../../config/database.php';
include_once '../../models/User.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->phone_or_email) && !empty($data->password)) {
    $user->phone_or_email = $data->phone_or_email;
    $user->password = $data->password; // Pass plaintext password to check in User::login()

    if($user->login()) {
        http_response_code(200);
        echo json_encode(array(
            "message" => "Successful login.",
            "user" => array(
                "id" => $user->id,
                "full_name" => $user->full_name,
                "phone_or_email" => $user->phone_or_email
            )
        ));
    } else {
        http_response_code(401);
        echo json_encode(array("message" => "Login failed. Incorrect credentials."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Incomplete data. Please provide phone_or_email and password."));
}
?>
