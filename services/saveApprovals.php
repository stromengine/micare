<?php
require '../vendor/autoload.php'; // Include the library
use \Firebase\JWT\JWT;
use Firebase\JWT\Key;

require '../config/db.php'; // Include the database connection file
// Check if it's a POST request
$headers = getallheaders();
if (isset($headers['Authorization'])) {
    $authorizationHeader = $headers['Authorization'];
    if (preg_match('/Bearer (.+)/', $authorizationHeader, $matches)) {
        $jwt = $matches[1];
        $user = JWT::decode($jwt, new Key($key, 'HS256'));
        $rawPostData = file_get_contents('php://input');
        $dataArray = json_decode($rawPostData, true);
        $date = $inserted_at ?? date('Y-m-d H:i:s');
        foreach ($dataArray["data"]["selectedIds"] as $d) {
            $updateQuery = "UPDATE stock_document
            SET is_approved = $1,
            approved_date = $2,
            payment_tracking=$3
            WHERE id = " . $d;

            $result = pg_query_params($conn, $updateQuery, array(
                $user->id,
                $date,
                $dataArray["data"]["paymentTrackingID"]
            ));
        }

        $data = array(
            "message" => "ok"
        );
        header('Content-Type: application/json');
        echo json_encode(array("data" => $data));
    } else {
        echo 'Invalid or unsupported authorization format';
    }
} else {
    echo 'Authorization header is not present in the request';
}
