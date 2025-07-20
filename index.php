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
echo "PHP backend is running.";
?>