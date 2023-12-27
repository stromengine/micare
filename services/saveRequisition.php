<?php
require '../vendor/autoload.php'; // Include the library
use \Firebase\JWT\JWT;
use Firebase\JWT\Key;

require '../config/db.php'; // Include the database connection file

$headers = getallheaders();
if (isset($headers['Authorization'])) {
    $authorizationHeader = $headers['Authorization'];
    if (preg_match('/Bearer (.+)/', $authorizationHeader, $matches)) {
        $jwt = $matches[1];
        $user = JWT::decode($jwt, new Key($key, 'HS256'));
        $rawPostData = file_get_contents('php://input');
        $dataArray = json_decode($rawPostData, true);
        // Check for duplicate inv_no before insertion
        $checkDuplicateQuery = "SELECT COUNT(*) FROM public.stock_document WHERE inv_no = $1";
        $checkDuplicateResult = pg_query_params($conn, $checkDuplicateQuery, array($dataArray["data"]["localUniqueId"]));
        $duplicateCount = pg_fetch_result($checkDuplicateResult, 0, 0);

        if ($duplicateCount === '0') {

            $query = "
        SELECT 
            MAX(CASE 
                WHEN type = 'Requisition' THEN SUBSTRING(code, 5)::int
                ELSE 0
            END) AS max_count
        FROM 
            stock_document
        WHERE 
            type = 'Requisition'";

            $result = pg_query($conn, $query);
            $data = pg_fetch_assoc($result);
            $count = $data['max_count'] + 1;
            $code = 'REQ-' . sprintf('%05d', $count);

            $insertQuery = "INSERT INTO public.stock_document (date, type, crp_id,store_id, to_crp_id,to_store_id, remarks, inserted_at,mwra_id,req_id,inv_no,payment_tracking,dc_no,source,code)
        VALUES ($1, $2, $3, $4, $5, $6,$7,$8,$9,$10,$11,$12,$13,$14,$15) RETURNING id";
            $date = $inserted_at ?? date('Y-m-d H:i:s');
            $result = pg_query_params($conn, $insertQuery, array(
                $date,
                "Requisition",
                $user->id,
                null,
                null,
                null,
                "Entered From Device",
                $date,
                null,
                null,
                $dataArray["data"]["localUniqueId"],
                $dataArray["data"]["paymentTracking"],
                $dataArray["data"]["orderNumber"],
                'mobile',
                $code 
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
                    $product["amount"],
                    $product["totalAmount"],
                ));

                $stock_document_detail_id = pg_fetch_result($result, 0, 0);
            }


            $response = array(
                'status' => 'ok'
            );

            // Encode the array as JSON and echo it
            echo json_encode($response);
        }
    } else {
        echo 'Invalid or unsupported authorization format';
    }
} else {
    echo 'Authorization header is not present in the request';
}
