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
        
        $clusterCode = "SELECT clusters.code from crps inner join clusters on clusters.id=crps.cluster_id where crps.id=" . $user->id;
        $clusterResult = pg_query($conn, $clusterCode);
        $cluster = pg_fetch_all($clusterResult);
        $prefix = explode('-', $cluster[0]["code"])[0];
        $query = "SELECT clusters.code, crps.name,crps.cluster_id, stock_document.* 
        FROM stock_document
        inner join crps on stock_document.crp_id=crps.id
        inner join clusters on clusters.id=crps.cluster_id
        where type='Requisition' and is_approved is null and clusters.code like '".$prefix."%'";
        $result = pg_query($conn, $query);
        
        while ($row = pg_fetch_assoc($result)) {
            $detailsQuery = "select products.name_en as productName, stock_document_details.id, stock_document_details.stock_document_id, stock_document_details.product_id, stock_document_details.quantity, stock_document_details.unit_price, stock_document_details.amount from stock_document_details inner join products on products.id=stock_document_details.product_id where stock_document_id=" . $row["id"];
            $detailsResult = pg_query($conn, $detailsQuery);
            $details = pg_fetch_all($detailsResult);
       
            if ($details) {
                foreach ($details as $key => $detail) {
                    // Convert quantity, amount, id, and unit_price to double in each associative array
                    $details[$key]['productId'] = (int)$detail['product_id'];
                    $details[$key]['quantity'] = (double)$detail['quantity'];
                    $details[$key]['amount'] = (double)$detail['amount'];
                    $details[$key]['id'] = (int)$detail['id'];
                    $details[$key]['unit_price'] = (double)$detail['unit_price'];
                }
            }
            $data["data"]["orderDetail"][] = array(
                "id" => $row["id"],
                "reqNo" => 'REQ-'.str_pad($row["id"], 3, '0', STR_PAD_LEFT),
                "clusterCode" => $row["code"],
                "crpName" => $row["name"],
                "orderDate" => $row["date"],
                "orderNo" => $row["dc_no"],
                "orderedProducts" => $details
            );
        }
        if (empty($data["data"]["orderDetail"])) {
            $data["data"]["orderDetail"] = array();
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
