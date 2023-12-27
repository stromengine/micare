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
        //[id] => 7
        //[username] => JCD001
        //[title] => crp
        $cluster_id = $_GET["cluster_ids"];
        $query = "select sales.* from sales  inner join crps on crps.id=sales.crp_id where  crps.cluster_id in (" . $cluster_id . ") limit 200";
        $result = pg_query($conn, $query);
        $data["data"] = [];
        $i = 0;
        while ($row = pg_fetch_assoc($result)) {
            $data["data"][$i]["crpId"] = (int)$row["crp_id"];
            $data["data"][$i]["id"] = (int)$row["id"];
            $data["data"][$i]["insertedAt"] =  str_replace(' ', 'T', $row["inserted_at"]);
            $data["data"][$i]["isDeleted"] = $row["is_deleted"]=="t" ? true : false;
            $data["data"][$i]["localUniqueId"] = $row["local_unique_id"];
            $data["data"][$i]["mwraId"] = (int)$row["mwra_id"];
            $data["data"][$i]["saleDate"] = str_replace(' ', 'T', $row["sale_date"]);
            $data["data"][$i]["status"] = $row["status"];
            $data["data"][$i]["updatedAt"] = str_replace(' ', 'T', $row["updated_at"]);
            $data["data"][$i]["saleItems"] = [];
            $query = "select * from sale_items where sale_id=" . $row['id'];
            $sale_items_result = pg_query($conn, $query);
            $sale_items = pg_fetch_all($sale_items_result);
            if(is_array($sale_items) && count($sale_items))
            foreach($sale_items as $s){
                $data["data"][$i]["saleItems"][] =array(
                    "amount"=> (float)$s["amount"]/100,
                    "id"=> (int)$s["id"],
                    "productId"=> (int)$s["product_id"],
                    "quantity"=> (float)$s["quantity"]
                );
            }
         
            // $data[$i]['sale_items'] = $sale_items;
            $i++;
        }
        $data["status"] = "ok";
        echo json_encode($data);
    } else {
        echo 'Invalid or unsupported authorization format';
    }
} else {
    echo 'Authorization header is not present in the request';
}
