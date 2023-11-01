<?php
header('Content-Type: application/json');
// Database connection parameters
$dbHost = '35.198.249.171';
$dbPort = '5432';
$dbName = 'micare_prod';
$dbUser = 'postgres';
$dbPass = 'Rspn@123';

// Connect to the database
$conn = pg_connect("host=$dbHost port=$dbPort dbname=$dbName user=$dbUser password=$dbPass");

if (!$conn) {
    die("Connection failed: " . pg_last_error());
}

$key = 'keytosuccess123';

?>