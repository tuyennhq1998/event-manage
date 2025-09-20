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
        die('Ban khong co quyen truy cap trang nay');
    }
}

function gui_email_don_gian($to, $subject, $message) {
    // Gửi email bằng mail(). Nếu host không hỗ trợ, thay bằng PHPMailer.
    global $cfg_email_from;
    $headers = 'From: ' . $cfg_email_from . "\r\n" .
               'Reply-To: ' . $cfg_email_from . "\r\n" .
               'MIME-Version: 1.0' . "\r\n" .
               'Content-type: text/html; charset=utf-8' . "\r\n";
    @mail($to, $subject, $message, $headers);
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
  