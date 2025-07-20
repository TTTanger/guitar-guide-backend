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
require_once 'mysql.php';


$sql = "SELECT key_name, lang, value FROM translations";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$translations = [];
function setNestedValue(&$array, $path, $value) {
    $keys = explode('.', $path);
    $temp = &$array;
    foreach ($keys as $key) {
        if (!isset($temp[$key])) $temp[$key] = [];
        $temp = &$temp[$key];
    }
    $temp = $value;
}

foreach ($result as $row) {
    $lang = $row['lang'];
    $key = $row['key_name'];
    $value = $row['value'];
    if (!isset($translations[$lang])) {
        $translations[$lang] = [];
    }
    setNestedValue($translations[$lang], $key, $value);
}
header('Content-Type: application/json');
echo json_encode($translations, JSON_UNESCAPED_UNICODE);
?>