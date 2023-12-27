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
        $rawPostData = file_get_contents('php://input');
        $dataArray = json_decode($rawPostData, true);

        $checkDuplicateQuery = "SELECT COUNT(*) FROM public.stock_document WHERE inv_no = $1";
        $checkDuplicateResult = pg_query_params($conn, $checkDuplicateQuery, array($dataArray["data"]["localUniqueId"]));
        $duplicateCount = pg_fetch_result($checkDuplicateResult, 0, 0);

        if ($duplicateCount === '0') {

            $insertQuery = "INSERT INTO public.stock_document (date, type, crp_id,store_id, to_crp_id,to_store_id, remarks, inserted_at,mwra_id,req_id,inv_no,payment_tracking,dc_no)
        VALUES ($1, $2, $3, $4, $5, $6,$7,$8,$9,$10,$11,$12,$13) RETURNING id";
            $date = $inserted_at ?? date('Y-m-d H:i:s');
            $result = pg_query_params($conn, $insertQuery, array(
                $date,
                "Good Recieved Note",
                $user->id,
                null,
                null,
                null,
                "Entered From Device",
                $date,
                null,
                $dataArray["data"]["requisitionId"],
                $dataArray["data"]["localUniqueId"],
                $dataArray["data"]["paymentTracking"],
                null
            ));
            $stock_document_id = pg_fetch_result($result, 0, 0);

            $products = $dataArray["data"]["products"];
            foreach ($products as $product) {
                $insertQuery = "INSERT INTO public.stock_document_details (stock_document_id, product_id, quantity, unit_price, amount)
            VALUES ($1, $2, $3, $4, $5) RETURNING id";

                $result = pg_query_params($conn, $insertQuery, array(
                    $stock_document_id,
                    $product["productId"],
                    $product["quantity"],
                    $product["amount"] / $product["quantity"],
                    $product["totalAmount"],
                ));

                $stock_document_detail_id = pg_fetch_result($result, 0, 0);
            }


            echo "saved";
        }
    } else {
        echo 'Invalid or unsupported authorization format';
    }
} else {
    echo 'Authorization header is not present in the request';
}
