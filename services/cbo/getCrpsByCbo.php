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
        $cbo_id = $_GET["cbo_id"];
        $query = "select * from crps where cbo_id=". $cbo_id ;
        $result = pg_query($conn, $query);
        $d = pg_fetch_all($result);
        $data["data"] = [];
        foreach($d as $row){

            $qCluster = "select * from clusters where id=".$row["cluster_id"];
            $clusterResult = pg_query($conn, $qCluster);
            $cluster = pg_fetch_all($clusterResult);
            $clusters = [];
            foreach($cluster as $c){
                $qbasti = "select * from bastis where cluster_id=".$c["id"];
                $bastiResult = pg_query($conn, $qbasti);
                $basti = pg_fetch_all($bastiResult);
                $bastis = [];
                foreach($basti as $b){
                    $bArray = array(
                        "id" => (int)$b["id"],
                        "nameEn" => $b["name_en"],
                        "nameSd" => $b["name_sd"],
                        "nameUr" => $b["name_ur"],
                        "totalPopulation" => (int)$b["total_population"],
                        "totalHouseholds" => (int)$b["total_households"],
                  
                    );
                    $bastis[] = $bArray;
                }
                $cArray = array(
                    "code" => $c["code"],
                    "id" => (int)$c["id"],
                    "nameEn" => $c["name_en"],
                    "nameSd" => $c["name_sd"],
                    "nameUr" => $c["name_ur"],
                    "totalPopulation" => (int)$c["total_population"],
                    "villageNameEn" => $c["village_name_en"],
                    "villageNameSd" => $c["village_name_sd"],
                    "villageNameUr" => $c["village_name_ur"],
                    "bastis" =>$bastis
                );

                $clusters[] = $cArray; 
            }


            $initialArray = array(
                "address" => $row["address"],
                "adminId" => (int)$row["admin_id"],
                "cboId" => (int)$row["cbo_id"],
                "email" => $row["email"],
                "id" => (int)$row["id"],
                "insertedAt" => str_replace(' ', 'T', $row["inserted_at"]),
                "isDeleted" => (bool)$row["is_deleted"],
                "isSenior" => (bool)$row["is_senior"],
                "latlng" => $row["latlng"],
                "name" => $row["name"],
                "notes" => $row["notes"],
                "phone" => $row["phone"],
                "settings" => null,
                "user" => null,
                "userId" => (int)$row["user_id"],
                "cluster" =>$clusters[0]
                // Add other elements as needed...
            );
            $data["data"][] =$initialArray;
        }
        $data["status"] = "ok";
        echo json_encode($data);
    } else {
        echo 'Invalid or unsupported authorization format';
    }
} else {
    echo 'Authorization header is not present in the request';
}
