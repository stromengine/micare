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
        $data = json_decode($rawPostData, true)["followup"]; // Assuming 'followup' key in JSON

        // Extracting data from JSON
        $muac = isset($data['muac']) ? $data['muac'] : null;
        $followupDate = isset($data['followupDate']) ? $data['followupDate'] : null;
        $condition = isset($data['condition']) ? $data['condition'] : null;
        $isWomanConditionChanged = isset($data['isWomanConditionChanged']) ? $data['isWomanConditionChanged'] : null;
        $isWomanConditionChanged =    $isWomanConditionChanged == false ? 0 : 1;
        $isMeetingWithAnc = isset($data['isMeetingWithAnc']) ? $data['isMeetingWithAnc'] : null;
        $isMeetingWithAnc =    $isMeetingWithAnc == false ? 0 : 1;
        $crpId = isset($data['crpId']) ? $data['crpId'] : null;
        $mwraId = isset($data['mwraId']) ? $data['mwraId'] : null;
        $insertedAt = isset($data['insertedAt']) ? $data['insertedAt'] : date('Y-m-d H:i:s');
        $updatedAt = isset($data['updatedAt']) ? $data['updatedAt'] : date('Y-m-d H:i:s');
        $localUniqueId = isset($data['localUniqueId']) ? $data['localUniqueId'] : null;
        $totalFamilyMembers = isset($data['totalFamilyMembers']) ? $data['totalFamilyMembers'] : null;
        $expectedDeliveryDate = isset($data['expectedDeliveryDate']) ? $data['expectedDeliveryDate'] : null;
        $childrenInfo = isset($data['childrenInfo']) ? json_encode($data['childrenInfo']) : null;
        $isFamilyPlanning = isset($data['isFamilyPlanning']) ? $data['isFamilyPlanning'] : null;
        $isFamilyPlanning =    $isFamilyPlanning == false ? 0 : 1;
        $contraceptiveMethods = isset($data['contraceptiveMethods']) ? '{' . implode(",", $data['contraceptiveMethods']) . '}' : null;

        $checkDuplicateQuery = "SELECT COUNT(*) FROM followups WHERE local_unique_id = $1";
        $checkDuplicateResult = pg_query_params($conn, $checkDuplicateQuery, array($localUniqueId));
        $duplicateCount = pg_fetch_result($checkDuplicateResult, 0, 0);

        if ($duplicateCount === '0') {
            $insertQuery = "INSERT INTO followups (
                muac, followup_date, condition, is_woman_condition_changed, is_meeting_with_anc,
                crp_id, mwra_id, inserted_at, updated_at, local_unique_id, total_family_members,
                expected_delivery_date, children_info, is_family_planning, contraceptive_methods
            )
            VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15)";

            $result = pg_query_params($conn, $insertQuery, array(
                $muac,
                $followupDate,
                $condition,
                $isWomanConditionChanged,
                $isMeetingWithAnc,
                $crpId,
                $mwraId,
                $insertedAt,
                $updatedAt,
                $localUniqueId,
                $totalFamilyMembers,
                $expectedDeliveryDate,
                $childrenInfo,
                $isFamilyPlanning,
                $contraceptiveMethods
            ));

            if ($result) {
                $data = array(
                    "message" => "ok"
                );
                header('Content-Type: application/json');
                echo json_encode(array("data" => $data));
            }
        }
    } else {
        echo 'Invalid or unsupported authorization format';
    }
} else {
    echo 'Authorization header is not present in the request';
}
