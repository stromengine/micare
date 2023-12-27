<?php
require '../../vendor/autoload.php'; // Include the library
use \Firebase\JWT\JWT;
use Firebase\JWT\Key;

require '../../config/db.php'; // Include the database connection file
// Check if it's a POST request
$headers = getallheaders();
if (isset($headers['Authorization'])) {
    $authorizationHeader = $headers['Authorization'];
    if (preg_match('/Bearer (.+)/', $authorizationHeader, $matches)) {
        $jwt = $matches[1];
        $user = JWT::decode($jwt, new Key($key, 'HS256'));
        
        $products["data"] = [
            [
                "Product Picture" => "https://storage.googleapis.com/micare-prod/products_uploads/5.png",
                "productId" => "2",
                "productName" => "Calcium",
                "threshold" => 10,
                "availableQuantity" => 25,
                "opening" => 10,
                "stockIn" => 150,
                "stockOut" => 125
            ],
            [
                "Product Picture" => "https://storage.googleapis.com/micare-prod/products_uploads/5.png",
                "productId" => "1",
                "productName" => "Zinckorol",
                "threshold" => 10,
                "opening" => 10,
                "availableQuantity" => 25,
                "stockIn" => 150,
                "stockOut" => 125
            ],
        ];
        $products["status"] = "ok";
        // Convert the data to JSON format
        $jsonData = json_encode($products);
        
        // Set the response header to specify JSON content
        header('Content-Type: application/json');
        
        // Output the JSON data
        echo $jsonData;

        // $data["status"] = "ok";

        // // Replace keys with underscores to camelCase
        // $dataWithCamelCaseKeys = replaceKeysWithCamelCase($data);
        // echo json_encode($dataWithCamelCaseKeys);
    } else {
        echo 'Invalid or unsupported authorization format';
    }
} else {
    echo 'Authorization header is not present in the request';
}
