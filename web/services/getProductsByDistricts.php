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
        $district = $_GET["district_id"];
        $query = "SELECT products.*,attachments.url FROM products LEFT JOIN product_attachments on product_attachments.product_id=products.id LEFT JOIN attachments on attachments.id = product_attachments.attachment_id where district_id=".$district;;
        $result = pg_query($conn, $query);
        $data = pg_fetch_all($result);
        echo json_encode($data);
    } else {
        echo 'Invalid or unsupported authorization format';
    }
} else {
    echo 'Authorization header is not present in the request';
}
