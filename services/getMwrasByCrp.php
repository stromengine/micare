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
        //[id] => 7
        //[username] => JCD001
        //[title] => crp
        $query = "select * from mwras  where is_deleted=false and  crp_id=".$user->id;
        $result = pg_query($conn, $query);
        $data["data"] = pg_fetch_all($result);
        $data["status"] = "ok";
        $dataWithCamelCaseKeys = replaceKeysWithCamelCase($data);
        echo json_encode($dataWithCamelCaseKeys);
    } else {
        echo 'Invalid or unsupported authorization format';
    }
} else {
    echo 'Authorization header is not present in the request';
}
