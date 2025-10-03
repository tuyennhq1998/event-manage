<?php
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../model/DichVu.php';
$dv = new SuKien($ket_noi);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function json_fail($msg, $code=400){ http_response_code($code); echo json_encode(['thanh_cong'=>false,'thong_bao'=>$msg]); exit; }
function json_ok($msg){ echo json_encode(['thanh_cong'=>true,'thong_bao'=>$msg]); exit; }

try {
  $su_kien_id = (int)($_POST['su_kien_id'] ?? 0);
  $ho_ten = trim($_POST['ho_ten'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $so_dien_thoai = trim($_POST['so_dien_thoai'] ?? '');

  if ($su_kien_id <= 0 || $ho_ten === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_fail('Du lieu khong hop le');
  }

  $su_kien = $dv->lay_su_kien_theo_id($su_kien_id);
  if (!$su_kien) json_fail('Khong tim thay su kien', 404);

  $tt = $dv->tinh_trang_thai_su_kien($su_kien['thoi_gian_bat_dau'], $su_kien['thoi_gian_ket_thuc']);
  if ($tt !== 'sap_toi') json_fail('Su kien khong con mo dang ky', 409);

  // Luu dang ky
  global $ket_noi;
  $user_id = $_SESSION['user_id'] ?? null;
  $sql = 'INSERT INTO event_registrations (su_kien_id, user_id, ho_ten, email, so_dien_thoai) VALUES (?, ?, ?, ?, ?)';
  $stm = $ket_noi->prepare($sql);
  $stm->execute([$su_kien_id, $user_id, $ho_ten, $email, $so_dien_thoai]);

  // Email cho người đăng ký
  $nd_user = '<p>Chao ' . htmlspecialchars($ho_ten) . ',</p>'
           . '<p>Ban da dang ky thanh cong su kien: <b>' . htmlspecialchars($su_kien['tieu_de']) . '</b></p>'
           . '<p>Thoi gian: ' . htmlspecialchars($su_kien['thoi_gian_bat_dau']) . ' → ' . htmlspecialchars($su_kien['thoi_gian_ket_thuc']) . '</p>'
           . '<p>Dia diem: ' . htmlspecialchars($su_kien['dia_diem']) . '</p>';
  gui_email_don_gian($email, '[Xac nhan] Dang ky su kien', $nd_user);

  // Email cho admin
  global $cfg_email_admin;
  if (!empty($cfg_email_admin)) {
    $nd_admin = '<p>Co dang ky moi: <b>' . htmlspecialchars($su_kien['tieu_de']) . '</b></p>'
              . '<p>Ho ten: ' . htmlspecialchars($ho_ten) . '<br>Email: ' . htmlspecialchars($email)
              . '<br>SDT: ' . htmlspecialchars($so_dien_thoai) . '</p>';
    gui_email_don_gian($cfg_email_admin, '[Thong bao] Co nguoi dang ky su kien', $nd_admin);
  }

  json_ok('Dang ky thanh cong! Vui long kiem tra email.');
} catch (Throwable $e) {
  // Ghi log thực tế ở đây nếu cần
  json_fail('Co loi xay ra: ' . $e->getMessage(), 500);
}