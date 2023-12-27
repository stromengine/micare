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
        $data = json_decode($rawPostData, true)["mwra"];

        $id = isset($data['id']) ? $data['id'] : null;
        $name = isset($data['name']) ? $data['name'] : null;
        $age = isset($data['age']) ? $data['age'] : null;
        $muac = isset($data['muac']) ? $data['muac'] : null;
        $husbandName = isset($data['husbandName']) ? $data['husbandName'] : null;
        $phone = isset($data['phone']) ? $data['phone'] : null;
        $address = isset($data['address']) ? $data['address'] : null;
        $familyIncome = isset($data['familyIncome']) ? $data['familyIncome'] : null;
        $totalFamilyMembers = isset($data['totalFamilyMembers']) ? $data['totalFamilyMembers'] : null;
        $condition = isset($data['condition']) ? $data['condition'] : null;
        $expectedDeliveryDate = isset($data['expectedDeliveryDate']) ? $data['expectedDeliveryDate'] : null;
        $childrenInfo = isset($data['childrenInfo']) ? json_encode($data['childrenInfo']) : null;
        $contraceptiveMethods = isset($data['contraceptiveMethods']) ?  '{' . implode(",", $data['contraceptiveMethods']) . '}' : null;
        $registrationDate = isset($data['registrationDate']) ? $data['registrationDate'] : null;
        $isBispMember = isset($data['isBispMember']) ? $data['isBispMember'] : null;
        $isBispMember =    $isBispMember == false ? 0 : 1;
        $isBispNashonuma = isset($data['isBispNashonuma']) ? $data['isBispNashonuma'] : null;
        $isBispNashonuma =    $isBispNashonuma == false ? 0 : 1;
        $isMeetingWithAnc = isset($data['isMeetingWithAnc']) ? $data['isMeetingWithAnc'] : null;
        $isMeetingWithAnc =    $isMeetingWithAnc == false ? 0 : 1;
        $isFamilyPlanning = isset($data['isFamilyPlanning']) ? $data['isFamilyPlanning'] : null;
        $isFamilyPlanning =    $isFamilyPlanning == false ? 0 : 1;
        $isDeleted = isset($data['isDeleted']) ? $data['isDeleted'] : 0;
        $crpId = isset($data['crpId']) ? $data['crpId'] : null;
        $clusterId = isset($data['clusterId']) ? $data['clusterId'] : null;
        $bastiId = isset($data['bastiId']) ? $data['bastiId'] : null;
        $adminId = isset($data['adminId']) ? $data['adminId'] : null;
        $insertedAt = isset($data['insertedAt']) ? $data['insertedAt'] : date('Y-m-d H:i:s');
        $updatedAt = isset($data['updatedAt']) ? $data['updatedAt'] : date('Y-m-d H:i:s');
        $localAutoId = isset($data['localAutoId']) ? $data['localAutoId'] : null;
        $localUniqueId = isset($data['localUniqueId']) ? $data['localUniqueId'] : null;



        $checkDuplicateQuery = "SELECT COUNT(*) FROM public.mwras WHERE local_unique_id = $1";
        $checkDuplicateResult = pg_query_params($conn, $checkDuplicateQuery, array($localUniqueId));
        $duplicateCount = pg_fetch_result($checkDuplicateResult, 0, 0);

        if ($duplicateCount === '0') {

            $insertQuery = "INSERT INTO mwras (
                name, age, muac, husband_name, phone, address, family_income, total_family_members,
                condition, expected_delivery_date, children_info, contraceptive_methods, registration_date,
                is_bisp_member, is_bisp_nashonuma, is_meeting_with_anc, is_family_planning, is_deleted,
                crp_id, cluster_id, basti_id, admin_id, inserted_at, updated_at, local_auto_id, local_unique_id
            )
            VALUES (
                $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17, $18, $19, $20, $21, $22, $23, $24, $25, $26
            ) RETURNING id";

            $date = $insertedAt ?? date('Y-m-d H:i:s');
            $result = pg_query_params($conn, $insertQuery, array(
                $name,
                $age,
                $muac,
                $husbandName,
                $phone,
                $address,
                $familyIncome,
                $totalFamilyMembers,
                $condition,
                $expectedDeliveryDate,
                $childrenInfo,
                $contraceptiveMethods,
                $registrationDate,
                $isBispMember,
                $isBispNashonuma,
                $isMeetingWithAnc,
                $isFamilyPlanning,
                $isDeleted,
                $crpId,
                $clusterId,
                $bastiId,
                $adminId,
                $date, // inserted_at
                $updatedAt, // updated_at
                $localAutoId,
                $localUniqueId
            ));

            $id = pg_fetch_result($result, 0, 0);
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
