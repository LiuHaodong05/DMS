<?php
$host = 'localhost';
$user = 'root';
$pass = 'root';  // PHPStudy默认密码 root，如果是MySQL 8.0可能是空密码
$dbname = 'dorm_system';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}
$conn->set_charset("utf8");
?>