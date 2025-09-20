<?php
// Tạo kết nối PDO dùng cấu hình trong config.php
require_once __DIR__ . '/config.php';


try {
$ket_noi = new PDO(
'mysql:host=' . $cfg_db_host . ';dbname=' . $cfg_db_name . ';charset=utf8mb4',
$cfg_db_user,
$cfg_db_pass,
[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
} catch (PDOException $e) {
die('Khong the ket noi DB: ' . $e->getMessage());
}