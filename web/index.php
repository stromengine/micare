<?php
// Database connection parameters
$dbHost = '35.198.249.171 ';
$dbPort = '5432';
$dbName = 'test-micare-db';
$dbUser = 'postgres';
$dbPass = 'Rspn@123';

// Connect to the database
$conn = pg_connect("host=$dbHost port=$dbPort dbname=$dbName user=$dbUser password=$dbPass");

if (!$conn) {
    die("Connection failed: " . pg_last_error());
}

// Execute a SELECT query
$query = "SELECT * FROM admins";
$result = pg_query($conn, $query);

if (!$result) {
    die("Error in SQL query: " . pg_last_error());
}

// Fetch and process the data
while ($row = pg_fetch_assoc($result)) {
    // Process the data here
    print_r($row);
}

// Close the database connection
pg_close($conn);
?>
