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
        $ids = $_GET["ids"];
        $query = "select followups.* from followups 
        inner join crps on followups.crp_id=crps.id
        where crps.cluster_id in (" . $ids . ")";
        $result = pg_query($conn, $query);
        $d = pg_fetch_all($result);
        $data["data"] = [];
        foreach ($d as $row) {

            $initialArray = array(
                "childrenInfo" => $row["children_info"]==null? new stdClass() : json_decode($row["children_info"]),
                "condition" => $row["condition"],
                "contraceptiveMethods" => $row["contraceptive_methods"] == null ? new stdClass() : $row["contraceptive_methods"],
                "crpId" =>  (int)$row["crp_id"],
                "expectedDeliveryDate" => str_replace(' ', 'T', $row["expected_delivery_date"]),
                "id" =>  (int)$row["id"],
                "insertedAt" => str_replace(' ', 'T', $row["inserted_at"]) ,
                "isFamilyPlanning" =>  $row["is_family_planning"] == "t" ? true : false,
                "isMeetingWithAnc" => $row["is_meeting_with_anc"] == "t" ? true : false,
                "isWomanConditionChanged" => $row["is_woman_condition_changed"] == "t" ? true : false,
                "localUniqueId" => $row["local_unique_id"],
                "muac" => (float)$row["muac"] / 100,
                "mwraId" => (int)$row["mwra_id"],
                "totalFamilyMembers" => $row["total_family_members"],
                "updatedAt" => str_replace(' ', 'T', $row["updated_at"]),
            );

            $data["data"][] =  $initialArray;
        }
        $data["status"] = "ok";
        echo json_encode($data);
    } else {
        echo 'Invalid or unsupported authorization format';
    }
} else {
    echo 'Authorization header is not present in the request';
}
