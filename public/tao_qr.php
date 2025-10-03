<?php
// public/tao_qr.php
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../model/DichVu.php';
$dv = new SuKien($ket_noi);

header('Content-Type: application/json; charset=utf-8');

try {
  $su_kien_id = (int)($_POST['su_kien_id'] ?? 0);
  $ho_ten     = trim($_POST['ho_ten'] ?? '');
  $email      = trim($_POST['email'] ?? '');
  $sdt        = trim($_POST['so_dien_thoai'] ?? '');

  if ($su_kien_id <= 0 || $ho_ten === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['ok' => false, 'msg' => 'Dữ liệu không hợp lệ']);
    exit;
  }

  $sk = $dv->lay_su_kien_theo_id($su_kien_id);
  if (!$sk) {
    echo json_encode(['ok' => false, 'msg' => 'Không tìm thấy sự kiện']);
    exit;
  }

  // Số tiền
  $amount = (int)($sk['gia'] ?? 0); // 0 = miễn phí
  $amount_text = $amount > 0 ? number_format($amount, 0, ',', '.') . ' đ' : 'Miễn phí';

  // Nội dung chuyển khoản (dùng để đối soát thủ công hoặc tự động nếu bạn có cron)
  // Ví dụ: SK{ID}-{Unix}-RAND
  $ref = 'SK' . $su_kien_id . '-' . time() . '-' . substr(bin2hex(random_bytes(3)), 0, 6);
  $noi_dung = $ref . ' ' . mb_substr($ho_ten, 0, 30);

  // Dựng ảnh QR bằng VietQR (thay bằng tài khoản của bạn)
  // Cấu hình trong config.php:
  // $cfg_vietqr_bank = '970415'; // hoặc mã bank VietQR (VD: "vcb" nếu dùng slug)
  // $cfg_vietqr_acno = '0123456789';
  // $cfg_vietqr_acname = 'TEN CHU TAI KHOAN';
  global $cfg_vietqr_bank, $cfg_vietqr_acno, $cfg_vietqr_acname;

  // Dùng endpoint ảnh tĩnh của vietqr.io (nếu không có net, ảnh có thể không load nhưng JSON vẫn trả ok):
  // Định dạng slug: image/{bank}-{acc}-compact2.png?amount=...&addInfo=...
  // Tham khảo bank slug tại vietqr.io
  $bank = urlencode($GLOBALS['bank'] ?? 'vcb'); // ví dụ 'vcb'
  $acc  = urlencode($GLOBALS['acc'] ?? '9772781926');
  $addInfo = urlencode($noi_dung);
  $amt = max(0, $amount);

  $qr_img = "https://img.vietqr.io/image/{$bank}-{$acc}-compact2.png?amount={$amt}&addInfo={$addInfo}";

  echo json_encode([
    'ok'          => true,
    'qr_img'      => $qr_img,
    'amount'      => $amount,
    'amount_text' => $amount_text,
    'noi_dung'    => $noi_dung,
    'tham_chieu'  => $ref
  ]);
} catch (Throwable $e) {
  echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}