<?php
header('Content-Type: application/json');
// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}
// Database connection parameters
$dbHost = 'localhost';
//$dbHost = '127.0.0.1';
// $dbHost = '/cloudsql/micare-pk:asia-southeast1:test-micare-db';
$dbPort = '5432';
$dbName = 'postgres';
$dbUser = 'postgres';
$dbPass = 'Rspn@123';
// micare-pk:asia-southeast1:test-micare-db
// Connect to the database
$conn = pg_connect("host=$dbHost port=$dbPort dbname=$dbName user=$dbUser password=$dbPass");

if (!$conn) {
    die("Connection failed: " . pg_last_error());
}

$key = 'keytosuccess123';


function replaceKeysWithCamelCase($data) {
    $newData = array();
    foreach ($data as $key => $value) {
        // Replace underscores with camelCase
        $newKey = lcfirst(str_replace('_', '', ucwords($key, '_')));
        if (is_array($value)) {
            $newData[$newKey] = replaceKeysWithCamelCase($value);
        } else {
            $newData[$newKey] = $value;
        }
    }
    return $newData;
}
?>