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
        $ids = $_GET["cbo_id"];
        $query = "select * from meetings  where cbo_id=" . $ids;
        $result = pg_query($conn, $query);
        $d = pg_fetch_all($result);
        $data["data"] = [];
        foreach ($d as $row) {

            $initialArray = array(
                "cboId" => (int)$row["cbo_id"] == 0 ? null : (int)$row["cbo_id"],
                "clusterId" => (int)$row["cluster_id"],
                "crpId" => (int)$row["crp_id"] == 0 ? null : (int)$row["crp_id"],
                "id" => (int)$row["id"],
                "insertedAt" => str_replace(' ', 'T', $row["inserted_at"]),
                "isDeleted" => (bool)$row["is_deleted"],
                "localUniqueId" => $row["local_unique_id"],
                "meetingDate" => str_replace(' ', 'T', $row["meeting_date"]),
                "organizer" => $row["organizer"],
                "title" => $row["title"],
                "totalInLawsParticipants" => (int)$row["total_in_laws_participants"],
                "totalParticipants" => (int)$row["total_participants"],
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
