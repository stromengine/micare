<?php
require '../vendor/autoload.php'; // Include the library
use \Firebase\JWT\JWT;

require '../config/db.php'; // Include the database connection file
// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the JSON data from the request body
    $json_data = file_get_contents('php://input');

    // Check if the JSON data is not empty
    if (!empty($json_data)) {
        // Attempt to decode the JSON data
        $params = json_decode($json_data, true); // Use `true` to decode as an associative array

        if ($params !== null) {
            // JSON data was successfully decoded
            // You can access the data as an associative array
            $username = $params['user']['username'];
            $password = $params['user']['password'];
            $clientType = $params['user']['clientType'];
            // Execute a SELECT query
            $query = "select * from users where title='" . $clientType . "' and username='" . $username . "'";
            $result = pg_query($conn, $query);
            if (!$result) {
                die("Error in SQL query: " . pg_last_error());
            }
            $recordCount = pg_num_rows($result);
            if ($recordCount != 0) {
                $user = pg_fetch_assoc($result);
                if ($clientType == 'crp') {
                    $query = "select * from crps where user_id=" .   $user["id"];
                    $crpResult = pg_query($conn, $query);
                    $crp = pg_fetch_assoc($crpResult);

                    $query = "select * from clusters where id=" .   $crp["cluster_id"];
                    $clusterResult = pg_query($conn, $query);
                    $cluster = pg_fetch_assoc($clusterResult);

                    $query = "select * from bastis where cluster_id=" .   $cluster["id"];
                    $bastiResult = pg_query($conn, $query);
                    $basti = pg_fetch_all($bastiResult);

                    // Payload data for the JWT
                    $payload = array(
                        "id" => $crp["id"],
                        "username" =>  $user["username"],
                        "title" =>  $user["title"],
                    );
                    // Generate the JWT
                    $jwt = JWT::encode($payload, $key, 'HS256');
                    $data["data"]["user"] = $crp;
                    $data["data"]["user"]["cluster"] = $cluster;
                    $data["data"]["user"]["cluster"]["bastis"] =  $basti;
                    $data["data"]["jwt"] = $jwt;
                    $data["status"] = "ok";
                } else if ($clientType == 'cbo') {
                    $query = "select * from cbos where user_id=" .   $user["id"];
                    $cboResult = pg_query($conn, $query);
                    $cbo = pg_fetch_assoc($cboResult);

                    $query = "select clusters.* from cluster_mappings
                    inner join clusters on clusters.id = cluster_mappings.cluster_id
                    where cluster_mappings.cbo_id=" .   $cbo["id"];
                    $clusterResult = pg_query($conn, $query);
                    $clusters = pg_fetch_all($clusterResult);
                    


                    $districtQ = "select * from districts where id=" .   $cbo["district_id"];
                    $districtResult = pg_query($conn, $districtQ);
                    $district = pg_fetch_all($districtResult);

                    $data["data"]["user"]["district"]["id"] = (int)$district[0]["id"];
                    $data["data"]["user"]["district"]["nameEn"] = $district[0]["name_en"];
                    $data["data"]["user"]["district"]["nameSd"] = $district[0]["name_sd"];
                    $data["data"]["user"]["district"]["nameUr"] = $district[0]["name_ur"];


                    // Payload data for the JWT
                    $payload = array(
                        "id" => $user["id"],
                        "username" =>  $user["username"],
                        "title" =>  $user["title"],
                    );
                    // Generate the JWT
                    $jwt = JWT::encode($payload, $key, 'HS256');
                    $data["data"]["user"]["address"] = $cbo["address"];
                    $data["data"]["user"]["adminId"] = (int)$cbo["admin_id"];
                    $data["data"]["user"]["email"] = $cbo["email"];
                    $data["data"]["user"]["id"] = (int)$cbo["id"];
                    $data["data"]["user"]["name"] = $cbo["name"];
                    $data["data"]["user"]["notes"] = $cbo["notes"];
                    $data["data"]["user"]["permissions"] = array();
                    $data["data"]["user"]["settings"] = array();
                    $data["data"]["user"]["title"] = "cbo";
                    $data["data"]["user"]["uid"] =(int)$cbo["user_id"];
                    $data["data"]["user"]["username"] =$user["username"];


                    $data["data"]["user"]["clusters"] = [];
                    foreach($clusters as $cluster){
                        $d= array(
                            "id" => (int)$cluster["id"],
                            "nameEn" => $cluster["name_en"],
                            "nameUr" => $cluster["name_ur"],
                            "nameSd" => $cluster["name_sd"],
                            "villageNameEn" => $cluster["village_name_en"],
                            "villageNameUr" => $cluster["village_name_ur"],
                            "villageNameSd" =>$cluster["village_name_sd"],
                            "code" => $cluster["code"],
                            "disabled" => (bool)$cluster["disabled"],
                            "totalPopulation" => (int)$cluster["total_population"],
                            "unionCouncilId" => (int)$cluster["union_council_id"],
                            "insertedAt" => $cluster["inserted_at"],
                            "updatedAt" => $cluster["updated_at"]
                        );
                        $data["data"]["user"]["clusters"][] =$d; 
                    }

                    $data["data"]["jwt"] = $jwt;
                    $data["status"] = "ok";
                }
                $dataWithCamelCaseKeys = replaceKeysWithCamelCase($data);
                echo json_encode($dataWithCamelCaseKeys);
            } else {
                $message = array(
                    "message" => "Invalid username or password",
                    "status" => "error"
                );
                $json_message = json_encode($message);
                $dataWithCamelCaseKeys = replaceKeysWithCamelCase($json_message);
                echo json_encode($dataWithCamelCaseKeys);
            }
        } else {
            // JSON data could not be decoded
            echo "Failed to decode JSON data.";
        }
    } else {
        // No JSON data received
        echo "No JSON data received.";
    }
} else {
    // Not a POST request
    echo "This script only accepts POST requests.";
}
