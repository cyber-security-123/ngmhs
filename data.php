<?php
$servername = "localhost"; // Or your server IP
$username   = "shibpurh_school";   // Your database username
$password   = "@Shibpur1Kantabari9Patnitala66@";   // Your database password
$dbname     = "shibpurh_school";   // Your database name

// Connect to MySQL
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get all table names in the database
$tablesResult = $conn->query("SHOW TABLES");
$allData = [];

if ($tablesResult->num_rows > 0) {
    while ($tableRow = $tablesResult->fetch_array()) {
        $tableName = $tableRow[0];

        // Fetch all data from the table
        $dataResult = $conn->query("SELECT * FROM `$tableName`");

        if ($dataResult->num_rows > 0) {
            while ($row = $dataResult->fetch_assoc()) {
                $allData[$tableName][] = $row;
            }
        } else {
            $allData[$tableName] = [];
        }
    }
}

// Display all data
echo "<pre>";
print_r($allData);
echo "</pre>";

$conn->close();
?>
