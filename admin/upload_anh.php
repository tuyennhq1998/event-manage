<?php
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../model/User.php';
$user = new User($ket_noi);

$user->bat_buoc_admin();

header('Content-Type: application/json; charset=utf-8');

$thu_muc = dirname(__DIR__) . '/uploads';
if (!is_dir($thu_muc)) mkdir($thu_muc, 0775, true);

if (empty($_FILES['file'])) { http_response_code(400); echo json_encode(['error'=>'Khong co file']); exit; }

$f = $_FILES['file'];
if ($f['error'] !== UPLOAD_ERR_OK) { http_response_code(400); echo json_encode(['error'=>'Loi upload: '.$f['error']]); exit; }

$mime = mime_content_type($f['tmp_name']);
$allow = ['image/jpeg','image/png','image/gif','image/webp'];
if (!in_array($mime, $allow)) { http_response_code(400); echo json_encode(['error'=>'Chi cho phep anh (jpg, png, gif, webp)']); exit; }

$ext = pathinfo($f['name'], PATHINFO_EXTENSION);
$ten = 'cover_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.' . strtolower($ext);
$path = $thu_muc . '/' . $ten;
move_uploaded_file($f['tmp_name'], $path);

$url = $cfg_base_url . '/uploads/' . $ten;
echo json_encode(['location' => $url]);
