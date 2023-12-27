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
        $district = $_GET["district_id"];
        $query = "SELECT products.* FROM products where district_id=".$district;
        $result = pg_query($conn, $query);
        while ($row = pg_fetch_assoc($result)) {
            // Process each row and add it to the data array
            $catQuery = "select * from categories where id=" . $row["category_id"];
            $catResult = pg_query($conn, $catQuery);
            $categories = pg_fetch_all($catResult);

            $manuQuery = "select * from manufacturers where id=" . $row["manufacturer_id"];
            $manuResult = pg_query($conn, $manuQuery);
            $manufacturers = pg_fetch_all($manuResult);

            $attQuery = "select attachments.* from product_attachments LEFT JOIN attachments on attachments.id = product_attachments.attachment_id where product_attachments.product_id=" . $row["category_id"];
            $attResult = pg_query($conn, $attQuery);
            $attachements = pg_fetch_all($attResult);
            $attArray =[];
            foreach($attachements as $att){
                $attArray[] = array(
                    "fileExtension" => $att["file_extension"],
                    "fileName" => $att["file_name"],
                    "id" => (int)$att["id"],
                    "sizeInBytes" => (int)$att["size_in_bytes"],
                    "url" => $att["url"],
                );
            }

            $data["data"][] = array(
                "attachments" => $attArray,
                "category" => array(
                    "adminId" =>(int)$categories[0]["admin_id"],
                    "id" =>(int)$categories[0]["id"],
                    "isDeleted" =>$categories[0]["is_deleted"],
                    "nameEn" =>$categories[0]["name_en"],
                    "nameSd" =>$categories[0]["name_sd"],
                    "nameUr" =>$categories[0]["name_ur"],
                ), // $categories[0],
                "description" => $row["description"],
                "districtId" => (int)$row["district_id"],
                "id" => (int)$row["id"],
                "adminId" => (int)$row["admin_id"],
                "insertedAt" => str_replace(' ', 'T', $row["inserted_at"]) ,
                "isDeleted" => $row["is_deleted"]=="f" ? false : true,
                "isWellma" => $row["is_wellma"]=="f" ? false : true,
                "manufacturer" =>  array(
                    "id" =>(int)$manufacturers[0]["id"],
                    "nameEn" =>$manufacturers[0]["name_en"],
                    "nameSd" =>$manufacturers[0]["name_sd"],
                    "nameUr" =>$manufacturers[0]["name_ur"],
                ),// $manufacturers[0],
                "margin" => (float)$row["margin"]/100,
                "nameEn" => $row["name_en"],
                "nameSd" => $row["name_sd"],
                "nameUr" => $row["name_ur"],
                "price" =>  (float)$row["price"]/100,
                "pricings" => array(),
                "sku" => $row["sku"],
                "status" => $row["status"],
                "tradePrice" => (float)$row["trade_price"]/100,
                "updatedAt" =>str_replace(' ', 'T', $row["updated_at"]) 
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
