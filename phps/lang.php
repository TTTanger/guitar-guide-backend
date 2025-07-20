<?php
$allowed_origins = [
    "https://www.guitar-guide.org",
    "https://guitar-guide-frontend-eqogsuppy-tangers-projects.vercel.app"
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Credentials: true");
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
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