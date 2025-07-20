<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

$host = getenv('DB_HOST');
$port = getenv('DB_PORT') ?: 5432;
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT NOW()");
    $now = $stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'message' => '数据库连接成功',
        'current_time' => $now
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => '数据库连接失败: ' . $e->getMessage()
    ]);
}
