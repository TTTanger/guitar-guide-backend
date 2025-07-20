<?php
require_once 'mysql.php';

$sql = "SELECT key_name, lang, value FROM translations";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

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

while ($row = $result->fetch_assoc()) {
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