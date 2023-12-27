<?php
require '../../vendor/autoload.php';
use \Firebase\JWT\JWT;
use Firebase\JWT\Key;

require '../../config/db.php';

$headers = getallheaders();

if (isset($headers['Authorization'])) {
    $authorizationHeader = $headers['Authorization'];
    if (preg_match('/Bearer (.+)/', $authorizationHeader, $matches)) {
        $jwt = $matches[1];
        $user = JWT::decode($jwt, new Key($key, 'HS256'));

        $cluster_id = $_GET["cluster_ids"];
        $query = "SELECT sales.id, status, sales.sale_date, sales.crp_id, sales.mwra_id, sales.inserted_at, sales.updated_at, sales.local_unique_id, sales.is_deleted, sale_items.amount, sale_items.id as sale_item_id, sale_items.product_id, sale_items.quantity FROM sales 
                  INNER JOIN crps ON crps.id = sales.crp_id
                  LEFT JOIN sale_items ON sale_items.sale_id = sales.id
                  WHERE crps.cluster_id IN ($cluster_id)";

        $result = pg_query($conn, $query);
        $data["data"] = [];
        
        while ($row = pg_fetch_assoc($result)) {
            $saleId = (int)$row["id"];
            if (!isset($data["data"][$saleId])) {
                $data["data"][$saleId] = [
                    "crpId" => (int)$row["crp_id"],
                    "id" => $saleId,
                    "insertedAt" => str_replace(' ', 'T', $row["inserted_at"]),
                    "isDeleted" => $row["is_deleted"] == "t" ? true : false,
                    "localUniqueId" => $row["local_unique_id"],
                    "mwraId" => (int)$row["mwra_id"],
                    "saleDate" => str_replace(' ', 'T', $row["sale_date"]),
                    "status" => $row["status"],
                    "updatedAt" => str_replace(' ', 'T', $row["updated_at"]),
                    "saleItems" => []
                    // Fill in other fields from the 'sales' table
                ];
            }

            if ($row["sale_item_id"] !== null) {
                $data["data"][$saleId]["saleItems"][] = [
                    "amount" => (float)$row["amount"] / 100,
                    "id" => (int)$row["sale_item_id"],
                    "productId" => (int)$row["product_id"],
                    "quantity" => (float)$row["quantity"]
                    // Fill in other fields from the 'sale_items' table
                ];
            }
        }

        $data["data"] = array_values($data["data"]); // reset keys
        $data["status"] = "ok";
        echo json_encode($data);
    } else {
        echo 'Invalid or unsupported authorization format';
    }
} else {
    echo 'Authorization header is not present in the request';
}

