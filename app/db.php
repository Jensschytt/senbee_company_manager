<?php
// app/db.php — SQLite connection and bootstrap


$DATA_DIR = __DIR__ . '/../data';
if (!is_dir($DATA_DIR)) {
mkdir($DATA_DIR, 0775, true);
}


$dbFile = $DATA_DIR . '/companies.db';
$initNeeded = !file_exists($dbFile);


try {
$pdo = new PDO('sqlite:' . $dbFile, null, null, [
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
PDO::ATTR_EMULATE_PREPARES => false,
]);
} catch (PDOException $e) {
http_response_code(500);
echo json_encode(['error' => 'Database connection failed', 'details' => $e->getMessage()]);
exit;
}


if ($initNeeded) {
$schema = file_get_contents(__DIR__ . '/schema.sql');
$pdo->exec($schema);
}