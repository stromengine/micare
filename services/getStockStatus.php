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

        $query = "SELECT products.id, products.name_en, products.name_sd, products.name_ur, products.price, products.margin, products.status, products.sku, products.description, products.is_deleted, products.admin_id, products.manufacturer_id, products.inserted_at, products.updated_at, products.is_wellma, products.trade_price, products.category_id, products.district_id, products.crpstock, products.storestock
        ,
        COALESCE((
        SELECT SUM(quantity) FROM stock_document_details 
        inner join stock_document on stock_document.id=stock_document_details.stock_document_id
        where to_crp_id=" . $user->id . " and is_approved is not null and product_id=products.id AND type in ('Stock Transfer Note')
        ),0) as stock_in,
        COALESCE(
        (SELECT SUM(quantity) FROM stock_document_details 
        inner join stock_document on stock_document.id=stock_document_details.stock_document_id
        where crp_id=" .  $user->id . " and product_id=products.id AND type in ('Sales Order')),0)
        stock_out,
        COALESCE((
        SELECT SUM(quantity) FROM stock_document_details 
        inner join stock_document on stock_document.id=stock_document_details.stock_document_id
        where to_crp_id=" . $user->id . " and is_approved is not null and product_id=products.id AND type in ('Stock Transfer Note')
        ),0)-COALESCE(
        (SELECT SUM(quantity) FROM stock_document_details 
        inner join stock_document on stock_document.id=stock_document_details.stock_document_id
        where crp_id=" .  $user->id . " and product_id=products.id AND type in ('Sales Order')),0)
        available_quantity
                from products";
        $result = pg_query($conn, $query);
        while ($row = pg_fetch_assoc($result)) {
            $attQuery = "select attachments.* from product_attachments LEFT JOIN attachments on attachments.id = product_attachments.attachment_id where product_attachments.product_id=" . $row["id"];
            $attResult = pg_query($conn, $attQuery);
            $attachements = pg_fetch_all($attResult);
            $products["data"][] = array(
                "Product Picture" =>    $attachements[0]["url"],
                "productId" =>$row["id"],
                "productName" => $row["name_en"],
                "threshold" => $row["crpstock"],
                "availableQuantity" =>  $row["available_quantity"],
                "stockIn" => $row["stock_in"],
                "stockOut" => $row["stock_out"]
            );
        }
      
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
