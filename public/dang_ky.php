<?php
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../model/DichVu.php';
$dv = new SuKien($ket_noi);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function json_fail($msg, $code = 400)
{
  http_response_code($code);
  echo json_encode(['thanh_cong' => false, 'thong_bao' => $msg]);
  exit;
}
function json_ok($msg)
{
  echo json_encode(['thanh_cong' => true, 'thong_bao' => $msg]);
  exit;
}

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
  $pay_amount   = isset($_POST['pay_amount']) ? (int)$_POST['pay_amount'] : null;
  $pay_ref      = trim($_POST['pay_ref'] ?? '');
  $pay_noi_dung = trim($_POST['pay_noi_dung'] ?? '');
  $pay_time     = date('Y-m-d H:i:s');

  $sql = 'INSERT INTO event_registrations 
          (su_kien_id, user_id, ho_ten, email, so_dien_thoai, pay_amount, pay_ref, pay_noi_dung, pay_status, pay_time) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
  $stm = $ket_noi->prepare($sql);
  $stm->execute([
    $su_kien_id,
    $user_id,
    $ho_ten,
    $email,
    $so_dien_thoai,
    $pay_amount,
    $pay_ref,
    $pay_noi_dung,
    'cho_xac_minh',
    $pay_time
  ]);
  $reg_id = (int)$ket_noi->lastInsertId();

  // 1) Tạo mã check-in duy nhất
  $checkin_code = 'SK' . $su_kien_id . '-R' . $reg_id . '-' . substr(bin2hex(random_bytes(3)), 0, 6);
  $stm2 = $ket_noi->prepare('UPDATE event_registrations SET checkin_code = ? WHERE id = ?');
  $stm2->execute([$checkin_code, $reg_id]);

  // 2) Sinh QR check-in (bắt buộc)
  $qr_checkin_url = tao_qr_qrserver($checkin_code);

  // 3) (Tuỳ chọn) QR thanh toán: nếu bạn có chuỗi thanh toán muốn nhúng luôn
  // Email cho người đăng ký
  $nd_user = '<p>Chào ' . htmlspecialchars($ho_ten) . ',</p>'
    . '<p>Bạn đã đăng ký thành công sự kiện: <b>' . htmlspecialchars($su_kien['tieu_de']) . '</b></p>'
    . '<p>Thời gian: ' . htmlspecialchars($su_kien['thoi_gian_bat_dau']) . ' → ' . htmlspecialchars($su_kien['thoi_gian_ket_thuc']) . '</p>'
    . '<p>Địa điểm: ' . htmlspecialchars($su_kien['dia_diem']) . '</p>'
    . '<hr>'
    . '<p><b>Mã check-in của bạn:</b> ' . htmlspecialchars($checkin_code) . '</p>'
    . '<p>Quét QR dưới đây khi đến tham dự:</p>'
    . '<p><img src="' . htmlspecialchars($qr_checkin_url) . '" alt="QR check-in" style="width:240px;height:240px;border:1px solid #eee;border-radius:8px"></p>';

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