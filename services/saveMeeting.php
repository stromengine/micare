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

        $checkDuplicateQuery = "SELECT COUNT(*) FROM meetings WHERE local_unique_id = $1";
        $checkDuplicateResult = pg_query_params($conn, $checkDuplicateQuery, array($dataArray["meeting"]["localUniqueId"]));
        $duplicateCount = pg_fetch_result($checkDuplicateResult, 0, 0);

        if ($duplicateCount === '0') {

            $date = new DateTime($dataArray["meeting"]["meetingDate"]);
            $todayDate = date("Y-m-d");
            $mysqlDate = $date->format('Y-m-d H:i:s');

            $insertQuery = "INSERT INTO meetings (title, meeting_date, 
            total_participants, total_in_laws_participants,
            organizer, is_deleted, crp_id, cbo_id, basti_id, 
            cluster_id, inserted_at, updated_at, local_unique_id)
        VALUES ($1, $2, $3, $4, $5, $6,$7,$8,$9,$10,$11,$12,$13) RETURNING id";
            $date = $inserted_at ?? date('Y-m-d H:i:s');
            $result = pg_query_params($conn, $insertQuery, array(
                $dataArray["meeting"]["title"],
                $mysqlDate,
                $dataArray["meeting"]["totalParticipants"],
                $dataArray["meeting"]["totalInLawsParticipants"],
                $dataArray["meeting"]["organizer"],
                0,
                $dataArray["meeting"]["crpId"],
                null,
                $dataArray["meeting"]["bastiId"],
                $dataArray["meeting"]["clusterId"],
                $todayDate,
                $todayDate,
                $dataArray["meeting"]["localUniqueId"]
            ));
            $stock_document_id = pg_fetch_result($result, 0, 0);


            $data = array(
                "message" => "ok"
            );
            header('Content-Type: application/json');
            echo json_encode(array("data" => $data));
        }
    } else {
        echo 'Invalid or unsupported authorization format';
    }
} else {
    echo 'Authorization header is not present in the request';
}
