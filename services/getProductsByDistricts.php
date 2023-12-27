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
        $query = "SELECT products.*,
         COALESCE((
        SELECT SUM(quantity) FROM stock_document_details 
        inner join stock_document on stock_document.id=stock_document_details.stock_document_id
        where to_crp_id=" . $user->id . "  and product_id=products.id AND type in ('Stock Transfer Note')
        ),0)-COALESCE(
        (SELECT SUM(quantity) FROM stock_document_details 
        inner join stock_document on stock_document.id=stock_document_details.stock_document_id
        where crp_id=" .  $user->id . " and product_id=products.id AND type in ('Sales Order')),0)
        available_quantity
         FROM products ";
		//inner join districts on districts.id=products.district_id where districts.prefix='".$district."'
        $result = pg_query($conn, $query);
        while ($row = pg_fetch_assoc($result)) {
            // Process each row and add it to the data array
            $catQuery = "select * from categories where id=" . $row["category_id"];
            $catResult = pg_query($conn, $catQuery);
            $categories = pg_fetch_all($catResult);

            $manuQuery = "select * from manufacturers where id=" . $row["manufacturer_id"];
            $manuResult = pg_query($conn, $manuQuery);
            $manufacturers = pg_fetch_all($manuResult);

            $attQuery = "select attachments.* from product_attachments LEFT JOIN attachments on attachments.id = product_attachments.attachment_id where product_attachments.product_id=" . $row["id"];
            $attResult = pg_query($conn, $attQuery);
            $attachements = pg_fetch_all($attResult);

            $data["data"][] = array(
                "attachments" => $attachements,
                "category" => $categories[0],
                "description" => $row["description"],
                "districtId" => $row["district_id"],
                "id" => $row["id"],
                "insertedAt" => $row["inserted_at"],
                "isDeleted" => $row["is_deleted"]=="f" ? false : true,
                "isWellma" => $row["is_wellma"]=="f" ? false : true,
                "manufacturer" => $manufacturers[0],
                "margin" => $row["margin"],
                "nameEn" => $row["name_en"],
                "nameSd" => $row["name_sd"],
                "nameUr" => $row["name_ur"],
                "price" => $row["price"]/100,
                "pricings" => array(),
                "availableQuantity" => (int)$row["available_quantity"], // To be replaced with actual available stock
                "sku" => $row["sku"],
                "status" => $row["status"],
                "tradePrice" => $row["trade_price"]/100,
                "updatedAt" => $row["updated_at"]
            );
        }

        $data["status"] = "ok";

        // Replace keys with underscores to camelCase
        $dataWithCamelCaseKeys = replaceKeysWithCamelCase($data);
        echo json_encode($dataWithCamelCaseKeys);
    } else {
        echo 'Invalid or unsupported authorization format';
    }
} else {
    echo 'Authorization header is not present in the request';
}
