<?php
// Cấu hình kết nối DB và cấu hình chung
$cfg_db_host = '127.0.0.1';
$cfg_db_name = 'wiftmbswhosting_temp';
$cfg_db_user = 'wiftmbswhosting_temp';
$cfg_db_pass = 'Tuyen@2021';


// Email gửi thông báo (tối thiểu). Lưu ý: hàm mail() phụ thuộc server
$cfg_email_from = 'tuyennhq@gmail.com';
$cfg_email_admin = 'tuyennhq@gmail.com';


// Đường dẫn gốc (điều chỉnh nếu đặt thư mục khác)
$cfg_base_url = 'https://web.sol9.site/';


// Bắt đầu session cho toàn dự án
if (session_status() === PHP_SESSION_NONE) {
session_start();
}