<?php
header('Content-Type: application/json');

// Reuse existing config to get DB connection and helpers
require_once __DIR__ . '/config.php';

$connected = false;
$tables = [];

if (isset($conn) && $conn) {
    $connected = true;
    $res = mysqli_query($conn, "SHOW TABLES");
    if ($res) {
        while ($row = mysqli_fetch_row($res)) {
            $tables[] = $row[0];
        }
    }
}

echo json_encode([
    'db_connected' => $connected,
    'tables' => $tables
]);

?>
