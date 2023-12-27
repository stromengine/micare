<?php
header('Content-Type: application/json');
// Database connection parameters
$dbHost = '35.198.249.171';
//$dbHost = '127.0.0.1';
// $dbHost = '/cloudsql/micare-pk:asia-southeast1:test-micare-db';
$dbPort = '5432';
$dbName = 'micare_prod';
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