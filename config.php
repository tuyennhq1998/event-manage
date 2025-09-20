<?php
// Cấu hình kết nối DB và cấu hình chung
$cfg_db_host = '127.0.0.1';
$cfg_db_name = 'quan_ly_su_kien';
$cfg_db_user = 'root';
$cfg_db_pass = '';


// Email gửi thông báo (tối thiểu). Lưu ý: hàm mail() phụ thuộc server
$cfg_email_from = 'tuyennhq@gmail.com';
$cfg_email_admin = 'tuyennhq@gmail.com';


// Đường dẫn gốc (điều chỉnh nếu đặt thư mục khác)
$cfg_base_url = '/event-manage';


// Bắt đầu session cho toàn dự án
if (session_status() === PHP_SESSION_NONE) {
session_start();
}