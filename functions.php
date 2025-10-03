<?php
// Các hàm dùng chung
require_once __DIR__ . '/db.php';

function gui_email_don_gian($to, $subject, $message) {
  global $cfg_email_from, $cfg_email_pass, $cfg_email_host, $cfg_email_port;

  // Require PHPMailer LAZY, đúng path cài thủ công (repo gốc)
  $base = __DIR__ . '/vendor/PHPMailer/src';
  if (!is_file("$base/PHPMailer.php") || !is_file("$base/SMTP.php") || !is_file("$base/Exception.php")) {
      error_log("PHPMailer files not found in $base");
      return false;
  }
  require_once "$base/PHPMailer.php";
  require_once "$base/SMTP.php";
  require_once "$base/Exception.php";

  // import class sau khi require
  if (!class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
      error_log("PHPMailer class not found after require");
      return false;
  }
  $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

  try {
      $mail->isSMTP();
      $mail->Host       = $cfg_email_host ?: 'smtp.gmail.com';
      $mail->SMTPAuth   = true;
      $mail->Username   = $cfg_email_from;   // Gmail
      $mail->Password   = $cfg_email_pass;   // App password
      $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port       = $cfg_email_port ?: 587;
      $mail->CharSet  = 'UTF-8';
      $mail->Encoding = 'base64';

      $mail->setFrom($cfg_email_from ?: 'no-reply@example.com', 'EventsPlant');
      $mail->addAddress($to);

      $mail->isHTML(true);
      $mail->Subject = $subject;
      $mail->Body    = $message;

      $mail->send();
      return true;
  } catch (\PHPMailer\PHPMailer\Exception $e) {
      error_log("Mailer Error: {$mail->ErrorInfo}");
      return false;
  } catch (\Throwable $e) {
      error_log("Mailer Throwable: " . $e->getMessage());
      return false;
  }
}

  
function get_option($key) {
    global $ket_noi;
    $stm = $ket_noi->prepare("SELECT opt_value FROM options WHERE opt_key = :k");
    $stm->execute([':k'=>$key]);
    return $stm->fetchColumn() ?: '';
}

$logo   = get_option('site_logo');
$banner = get_option('site_banner');
function update_option($key, $value) {
    global $ket_noi;
    $stm = $ket_noi->prepare("
        INSERT INTO options (opt_key, opt_value) VALUES (:k,:v)
        ON DUPLICATE KEY UPDATE opt_value=:v
    ");
    return $stm->execute([':k'=>$key, ':v'=>$value]);
}
function banners_all() {
    global $ket_noi;
    $sql = "SELECT opt_value FROM options WHERE opt_key = 'site_banner' LIMIT 1";
    $val = $ket_noi->query($sql)->fetchColumn();
    if (!$val) return []; // không có dữ liệu
    $arr = explode(",", $val);
    return $arr; // mảng URL banner
}

  function banner_add($path, $pos = null) {
    global $ket_noi;
    if ($pos === null) {
      $pos = (int)$ket_noi->query("SELECT COALESCE(MAX(vi_tri),0)+1 FROM banners")->fetchColumn();
    }
    $stm = $ket_noi->prepare("INSERT INTO banners(anh_url,vi_tri) VALUES(:u,:p)");
    $stm->execute([':u'=>$path, ':p'=>$pos]);
    return (int)$ket_noi->lastInsertId();
  }
  function banner_update_pos($id, $pos) {
    global $ket_noi;
    $stm = $ket_noi->prepare("UPDATE banners SET vi_tri=:p WHERE id=:id");
    return $stm->execute([':p'=>(int)$pos, ':id'=>(int)$id]);
  }
  function banner_delete($id) {
    global $ket_noi;
    $stm = $ket_noi->prepare("DELETE FROM banners WHERE id=:id");
    return $stm->execute([':id'=>(int)$id]);
  }
  function tao_qr_qrserver($text, $size = 300) {
    $sz = intval($size);
    $data = rawurlencode($text);
    return "https://api.qrserver.com/v1/create-qr-code/?size={$sz}x{$sz}&data={$data}";
}
