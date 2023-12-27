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
        $cbo_id = $_GET["cbo_id"];
        $query = "select mwras.* from mwras inner join crps on crps.id = mwras.crp_id where  crps.cbo_id=". $cbo_id;
        $result = pg_query($conn, $query);
        $mwra = pg_fetch_all($result);
        $data["data"] = [];
        foreach($mwra as $m){
            $data["data"][] = array(

            "id" => (int)$m["id"],
            "name" => $m["name"],
            "age" =>  (int)$m["age"],
            "muac" => (float)$m["muac"],
            "husband_name" => $m["husband_name"],
            "phone" => $m["phone"],
            "address" => $m["address"],
            "familyIncome" => $m["family_income"],
            "totalFamilyMembers" =>  (int)$m["total_family_members"],
            "condition" => $m["condition"],
            "expectedDeliveryDate" => str_replace(' ', 'T', $m["expected_delivery_date"]),
            "childrenInfo" => json_decode($m["children_info"]),
            "contraceptiveMethods" => json_decode($m["contraceptive_methods"]),
            "registrationDate" =>  str_replace(' ', 'T', $m["registration_date"]),
            "is_BispMember" => $m["is_bisp_member"]=="t" ?true : false,
            "isBispNashonuma" => $m["is_bisp_nashonuma"]=="t" ?true : false,
            "isMeetingWithAnc" => $m["is_meeting_with_anc"]=="t" ?true : false,
            "isFamilyPlanning" => $m["is_family_planning"]=="t" ?true : false,
            "isDeleted" => $m["is_deleted"]=="t" ?true : false,
            "crpId" => (int)$m["crp_id"],
            "clusterId" => (int)$m["cluster_id"],
            "bastiId" => (int)$m["basti_id"],
            "adminId" => (int)$m["admin_id"],
            "insertedAt" =>  str_replace(' ', 'T', $m["inserted_at"]),
            "updatedAt" =>str_replace(' ', 'T', $m["updated_at"]) ,
            "localAutoId" => (int)$m["local_auto_id"],
            "localUniqueId" => $m["local_unique_id"],
            );
        }
        $data["status"] = "ok";
        $dataWithCamelCaseKeys = replaceKeysWithCamelCase($data);
        echo json_encode($dataWithCamelCaseKeys);
    } else {
        echo 'Invalid or unsupported authorization format';
    }
} else {
    echo 'Authorization header is not present in the request';
}
