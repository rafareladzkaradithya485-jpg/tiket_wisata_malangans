<?php
// Safe environment checker for debugging DB host/port (no secrets printed)
// Usage: open /env_check.php on deployed service
error_reporting(E_ALL);
ini_set('display_errors', 1);

function mask($v) {
    if ($v === null || $v === '') return '(empty)';
    return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$vars = [
    'DB_HOST' => getenv('DB_HOST'),
    'MYSQL_HOST' => getenv('MYSQL_HOST'),
    'DB_PORT' => getenv('DB_PORT'),
    'MYSQL_PORT' => getenv('MYSQL_PORT'),
    'DB_NAME' => getenv('DB_NAME'),
    'MYSQL_DATABASE' => getenv('MYSQL_DATABASE'),
    'RAILWAY_DATABASE_URL' => getenv('DATABASE_URL') ?: getenv('RAILWAY_DATABASE_URL'),
];

header('Content-Type: text/plain; charset=utf-8');
echo "Environment debug (non-sensitive)\n";
echo "------------------------------\n";
foreach ($vars as $k => $v) {
    echo sprintf("%s: %s\n", $k, mask($v));
}

// Try a connection attempt (will not print password)
$host = getenv('DB_HOST') ?: getenv('MYSQL_HOST') ?: 'not-set';
$port = getenv('DB_PORT') ?: getenv('MYSQL_PORT') ?: '';
$user = getenv('DB_USER') ?: getenv('MYSQL_USER') ?: '(not shown)';
$db = getenv('DB_NAME') ?: getenv('MYSQL_DATABASE') ?: '(not shown)';

echo "\nConnection test:\n";
if ($host === 'not-set') {
    echo "Host not set in environment variables.\n";
    exit;
}

// Attempt TCP connection to host:port (suppress warning)
$tryPort = $port ? (int)$port : 3306;
$sock = @mysqli_init();
if (@mysqli_real_connect($sock, ($host === 'localhost' ? '127.0.0.1' : $host), $user, null, $db, $tryPort)) {
    echo "Connected to DB host={$host} port={$tryPort} (authentication attempted).\n";
    mysqli_close($sock);
} else {
    $err = mysqli_connect_error();
    echo "Connection FAILED to host={$host} port={$tryPort}. Error: " . mask($err) . "\n";
}

echo "\nNote: This endpoint masks credentials and is for short-lived debugging. Remove after use.\n";

?>
