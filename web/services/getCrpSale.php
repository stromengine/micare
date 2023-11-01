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
        $query = "select * from sales where crp_id=".$user->id;
        $result = pg_query($conn, $query);
        $data = [];
        $i=0;
        while ($row = pg_fetch_assoc($result)) {
          $data[$i] = $row;
          $query = "select * from sale_items where sale_id=".$row['id'];
          $sale_items_result = pg_query($conn, $query);
          $sale_items = pg_fetch_all($sale_items_result);
          $data[$i]['sale_items'] = $sale_items;
          $i++; 
        }

        echo json_encode($data);
    } else {
        echo 'Invalid or unsupported authorization format';
    }
} else {
    echo 'Authorization header is not present in the request';
}
