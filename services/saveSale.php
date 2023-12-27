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

        // Check for duplicate localUniqueId before insertion
        $checkDuplicateQuery = "SELECT  COUNT(*) FROM sales WHERE local_unique_id = $1";
        $checkDuplicateResult = pg_query_params($conn, $checkDuplicateQuery, array($dataArray["sale"]["localUniqueId"]));
        $existingSaleId = pg_fetch_result($checkDuplicateResult, 0, 0);

        if (!$existingSaleId) {
            // Insert into public.sales table
            $insertSalesQuery = "INSERT INTO sales (status, sale_date, crp_id, mwra_id, inserted_at,updated_at, local_unique_id)
            VALUES ($1, $2, $3, $4, $5, $6,$7) RETURNING id";

            $date = $inserted_at ?? date('Y-m-d H:i:s');
            $result = pg_query_params($conn, $insertSalesQuery, array(
                "completed",
                $dataArray["sale"]["saleDate"],
                $user->id,
                $dataArray["sale"]["mwraId"],
                $date,
                $date,
                $dataArray["sale"]["localUniqueId"]
            ));

            $saleId = pg_fetch_result($result, 0, 0);

            // Insert into public.sale_items table
            $saleItems = $dataArray["sale"]["saleItems"];

            foreach ($saleItems as $item) {
                $insertSaleItemsQuery = "INSERT INTO sale_items (quantity, amount, product_id, sale_id, inserted_at, updated_at)
                VALUES ($1, $2, $3, $4,$5,$6)";

                pg_query_params($conn, $insertSaleItemsQuery, array(
                    $item["quantity"],
                    $item["amount"],
                    $item["productId"],
                    $saleId,
                    $date,
                    $date
                ));
            }


            //Save Sales Order
            $query = "
            SELECT 
                MAX(CASE 
                    WHEN type = 'Sales Order' THEN SUBSTRING(code, 5)::int
                    ELSE 0
                END) AS max_count
            FROM 
                stock_document
            WHERE 
                type = 'Sales Order'";

            $result = pg_query($conn, $query);
            $data = pg_fetch_assoc($result);
            // Increment the count and generate the code
            $count = $data['max_count'] + 1;
            $code = 'SAL-' . sprintf('%05d', $count);

            $insertQuery = "INSERT INTO public.stock_document (date, type, crp_id,store_id, to_crp_id,to_store_id, remarks, inserted_at,mwra_id,req_id,inv_no,payment_tracking,dc_no,source,code)
            VALUES ($1, $2, $3, $4, $5, $6,$7,$8,$9,$10,$11,$12,$13,$14,$15) RETURNING id";
            $date = $inserted_at ?? date('Y-m-d H:i:s');
            $result = pg_query_params($conn, $insertQuery, array(
                $date,
                "Sales Order",
                $user->id,
                null,
                null,
                null,
                "Entered From Device",
                $date,
                $dataArray["sale"]["mwraId"],
                null,
                $dataArray["sale"]["localUniqueId"],
                null,
                null,
                'mobile',
                $code
            ));
            $stock_document_id = pg_fetch_result($result, 0, 0);

            $products = $dataArray["sale"]["saleItems"];

            foreach ($products as $product) {
                $insertQuery = "INSERT INTO public.stock_document_details (stock_document_id, product_id, quantity, unit_price, amount)
                VALUES ($1, $2, $3, $4, $5) RETURNING id";

                $result = pg_query_params($conn, $insertQuery, array(
                    $stock_document_id,
                    $product["productId"],
                    $product["quantity"],
                    $product["amount"],
                    $product["amount"] * $product["quantity"]
                ));

                $stock_document_detail_id = pg_fetch_result($result, 0, 0);
            }


            //End Save Sales order

            $response = array('status' => 'ok');
            // Encode the array as JSON and echo it
            echo json_encode($response);
        } else {
            echo 'Data with the same localUniqueId already exists.';
        }
    } else {
        echo 'Invalid or unsupported authorization format';
    }
} else {
    echo 'Authorization header is not present in the request';
}
