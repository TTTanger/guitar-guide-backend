<?php
// 允许指定来源跨域请求
header("Access-Control-Allow-Origin: https://www.guitar-guide.org");

// 如果你想允许所有来源（不推荐，安全风险），写成：
// header("Access-Control-Allow-Origin: *");

// 允许请求方法
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

// 允许请求头
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// 处理预检请求
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}
$host = getenv("DB_HOST");
$port = getenv("DB_PORT") ?: 5432;
$dbname = getenv("DB_NAME");
$user = getenv("DB_USER");
$pass = getenv("DB_PASS");

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;";
    $conn = new PDO($dsn, $user, $pass);

    // 设置错误模式
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}
?>
