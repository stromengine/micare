<?php
require '../../vendor/autoload.php'; // Include the library
use \Firebase\JWT\JWT;
use Firebase\JWT\Key;

require '../../config/db.php'; // Include the database connection file
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = json_decode(file_get_contents('php://input'), true);
    $inserted_at = date('Y-m-d H:i:s');
    $updated_at = date('Y-m-d H:i:s');
    $totalParticipants = $postData["meeting"]['totalParticipants']; // Assuming 'cboId' is part of the received data
    $meetingDate = $postData["meeting"]['meetingDate']; // Assuming 'clusterId' is part of the received data
    $organizer = $postData["meeting"]['organizer']; // Assuming 'organizer' is part of the received data
    $title = $postData["meeting"]['title']; // Assuming 'title' is part of the received data
    $cboId = $postData["meeting"]['cboId']; // Assuming 'title' is part of the received data
    $clusterId = $postData["meeting"]['clusterId']; // Assuming 'title' is part of the received data
    $localUniqueId = $postData["meeting"]['localUniqueId']; // Assuming 'title' is part of the received data
    $unique_record = "select * from meetings where local_unique_id ='" . $localUniqueId . "'";
    $unique_record_result = pg_query($conn, $unique_record);
    $recordCount = pg_num_rows($unique_record_result);
    if ($recordCount == 0) {
        // INSERT INTO public.meetings(
        //     id, title, meeting_date, total_participants, total_in_laws_participants, organizer, is_deleted, crp_id, cbo_id, basti_id, cluster_id, inserted_at, updated_at, local_unique_id)
        //     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);
        // Prepare the SQL INSERT statement
        // $insertQuery = "INSERT INTO meetings (title, meeting_date, total_participants, total_in_laws_participants, organizer, is_deleted,cbo_id,cluster_id, inserted_at, updated_at, local_unique_id) 
        //                 VALUES ($title,$meetingDate,$totalParticipants,0,$organizer,'FALSE',$cboId,$clusterId,$inserted_at,$updated_at,$localUniqueId)";
        $insertQuery = "INSERT INTO meetings (title, meeting_date, total_participants, total_in_laws_participants, organizer, is_deleted, cbo_id, cluster_id, inserted_at, updated_at, local_unique_id) 
    VALUES ($1, $2, $3, 0, $4, 'FALSE', $5, $6, $7, $8, $9)";
        // Execute the SQL INSERT statement
        $insertResult = pg_query_params($conn, $insertQuery, array(
            null,
            $meetingDate,
            $totalParticipants,
            $organizer,
            $cboId,
            $clusterId,
            $inserted_at,
            $updated_at,
            $localUniqueId
        ));
        if ($insertResult) {
            echo json_encode(array("status" => "ok", "message" => "Meeting data inserted successfully"));
        } else {
            echo json_encode(array("status" => "error", "message" => "Failed to insert meeting data"));
        }
    }
} else {
    echo json_encode(array("status" => "error", "message" => "Invalid request method"));
}
