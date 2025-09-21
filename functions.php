<?php
// Các hàm dùng chung
require_once __DIR__ . '/db.php';

function tim_user_theo_email($email) {
    global $ket_noi;
    $sql = 'SELECT * FROM users WHERE email = ? LIMIT 1';
    $stm = $ket_noi->prepare($sql);
    $stm->execute([$email]);
    return $stm->fetch(PDO::FETCH_ASSOC);
}

function tao_user($ten, $email, $mat_khau) {
    global $ket_noi;
    $hash = password_hash($mat_khau, PASSWORD_BCRYPT);
    $sql = 'INSERT INTO users (ten, email, mat_khau_hash) VALUES (?, ?, ?)';
    $stm = $ket_noi->prepare($sql);
    $stm->execute([$ten, $email, $hash]);
    return $ket_noi->lastInsertId();
}

function dang_nhap($email, $mat_khau) {
    $user = tim_user_theo_email($email);
    if ($user && password_verify($mat_khau, $user['mat_khau_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_ten'] = $user['ten'];
        $_SESSION['user_vai_tro'] = $user['vai_tro'];
        return true;
    }
    return false;
}

function bat_buoc_dang_nhap() {
    if (empty($_SESSION['user_id'])) {
        header('Location: /public/dang_nhap.php');
        exit;
    }
}

function bat_buoc_admin() {
    bat_buoc_dang_nhap();
    if (($_SESSION['user_vai_tro'] ?? 'user') !== 'admin') {
        die('Bạn không có quyền truy cập trang này');
    }
}

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

function tinh_trang_thai_su_kien($bat_dau, $ket_thuc) {
    // Trả về 'sap_toi' | 'dang_dien_ra' | 'da_ket_thuc'
    $now = new DateTime();
    $bd = new DateTime($bat_dau);
    $kt = new DateTime($ket_thuc);
    if ($now < $bd) return 'sap_toi';
    if ($now >= $bd && $now <= $kt) return 'dang_dien_ra';
    return 'da_ket_thuc';
}

function lay_su_kien_theo_id($id) {
    global $ket_noi;
    $stm = $ket_noi->prepare('SELECT * FROM events WHERE id = ?');
    $stm->execute([$id]);
    return $stm->fetch(PDO::FETCH_ASSOC);
}

function them_su_kien($tieu_de, $mo_ta, $dia_diem,$gia, $soluong, $bat_dau, $ket_thuc, $mo_ta_html=null, $anh_bia=null){
    global $ket_noi;
    $sql = "INSERT INTO events (tieu_de, mo_ta, dia_diem,gia, so_luong, thoi_gian_bat_dau, thoi_gian_ket_thuc, mo_ta_html, anh_bia)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stm = $ket_noi->prepare($sql);
    $stm->execute([$tieu_de, $mo_ta, $dia_diem, $gia, $soluong, $bat_dau, $ket_thuc, $mo_ta_html, $anh_bia]);
  }
  
  function cap_nhat_su_kien($id, $tieu_de, $mo_ta, $dia_diem, $gia, $soluong, $bat_dau, $ket_thuc, $mo_ta_html=null, $anh_bia=null){
    global $ket_noi;
    $sql = "UPDATE events SET tieu_de=?, mo_ta=?, dia_diem=?, gia=?, so_luong=?, thoi_gian_bat_dau=?, thoi_gian_ket_thuc=?, mo_ta_html=?, anh_bia=? WHERE id=?";
    $stm = $ket_noi->prepare($sql);
    $stm->execute([$tieu_de, $mo_ta, $dia_diem, $gia, $soluong, $bat_dau, $ket_thuc, $mo_ta_html, $anh_bia, $id]);
  }
  
function xoa_su_kien($id) {
    global $ket_noi;
    $stm = $ket_noi->prepare('DELETE FROM events WHERE id = ?');
    return $stm->execute([$id]);
}

function dem_nguoi_tham_gia($su_kien_id) {
    global $ket_noi;
    $stm = $ket_noi->prepare('SELECT COUNT(*) FROM event_registrations WHERE su_kien_id = ?');
    $stm->execute([$su_kien_id]);
    return (int)$stm->fetchColumn();
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
  /* ====== LIÊN HỆ ====== */
function tao_lien_he($ho_ten, $email, $so_dien_thoai, $tieu_de, $noi_dung){
    global $ket_noi;
    $sql = "INSERT INTO contacts (ho_ten, email, so_dien_thoai, tieu_de, noi_dung) VALUES (?,?,?,?,?)";
    $stm = $ket_noi->prepare($sql);
    return $stm->execute([$ho_ten, $email, $so_dien_thoai, $tieu_de, $noi_dung]);
  }
  
  function dem_lien_he($q=''){
    global $ket_noi;
    if ($q===''){
      return (int)$ket_noi->query("SELECT COUNT(*) FROM contacts")->fetchColumn();
    }
    $stm = $ket_noi->prepare("SELECT COUNT(*) FROM contacts WHERE (ho_ten LIKE :kw OR email LIKE :kw OR tieu_de LIKE :kw)");
    $stm->execute([':kw'=>"%$q%"]);
    return (int)$stm->fetchColumn();
  }
  
  function danh_sach_lien_he($page=1,$per_page=10,$q=''){
    global $ket_noi;
    $offset = ($page-1)*$per_page;
    $params=[]; $where='1=1';
    if ($q!==''){ $where.=" AND (ho_ten LIKE :kw OR email LIKE :kw OR tieu_de LIKE :kw)"; $params[':kw']="%$q%"; }
    $sql = "SELECT * FROM contacts WHERE $where ORDER BY ngay_gui DESC, id DESC LIMIT :lim OFFSET :off";
    $stm = $ket_noi->prepare($sql);
    foreach($params as $k=>$v) $stm->bindValue($k,$v);
    $stm->bindValue(':lim',(int)$per_page,PDO::PARAM_INT);
    $stm->bindValue(':off',(int)$offset,PDO::PARAM_INT);
    $stm->execute();
    return $stm->fetchAll(PDO::FETCH_ASSOC);
  }
  
  function lien_he_theo_id($id){
    global $ket_noi;
    $stm = $ket_noi->prepare("SELECT * FROM contacts WHERE id=:id");
    $stm->execute([':id'=>$id]);
    return $stm->fetch(PDO::FETCH_ASSOC);
  }
  
  function cap_nhat_trang_thai_lh($id,$tt='da_xu_ly'){
    global $ket_noi;
    $stm = $ket_noi->prepare("UPDATE contacts SET trang_thai=:tt WHERE id=:id");
    return $stm->execute([':tt'=>$tt, ':id'=>$id]);
  }
  
  function xoa_lien_he($id){
    global $ket_noi;
    $stm = $ket_noi->prepare("DELETE FROM contacts WHERE id=:id");
    return $stm->execute([':id'=>$id]);
  }
