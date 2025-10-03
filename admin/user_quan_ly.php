<?php
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../model/User.php';

$user = new User($ket_noi);

$user->bat_buoc_admin();

$base = $cfg_base_url;

/* ---------------- Helpers (fallback nếu functions.php của bạn chưa có) ---------------- */
if (!function_exists('tim_user_theo_id')) {
  function tim_user_theo_id($id){
    global $ket_noi;
    $stm = $ket_noi->prepare("SELECT id, ten, email, COALESCE(vai_tro,2) AS vai_tro FROM users WHERE id=:id");
    $stm->execute([':id'=>$id]);
    return $stm->fetch(PDO::FETCH_ASSOC) ?: null;
  }
}
if (!function_exists('tim_user_theo_email')) {
  function tim_user_theo_email($email){
    global $ket_noi;
    $stm = $ket_noi->prepare("SELECT id, ten, email, COALESCE(vai_tro,2) AS vai_tro FROM users WHERE email=:e");
    $stm->execute([':e'=>$email]);
    return $stm->fetch(PDO::FETCH_ASSOC) ?: null;
  }
}
if (!function_exists('tao_user')) {
  function tao_user($ten, $email, $mat_khau, $vai_tro = 2){
    global $ket_noi;
    $hash = password_hash($mat_khau, PASSWORD_BCRYPT);
    $stm = $ket_noi->prepare("INSERT INTO users(ten,email,mat_khau_hash,vai_tro,ngay_tao) VALUES(:t,:e,:mk,:vr,NOW())");
    $stm->execute([':t'=>$ten, ':e'=>$email, ':mk'=>$hash, ':vr'=>$vai_tro]);
    return (int)$ket_noi->lastInsertId();
  }
}
if (!function_exists('cap_nhat_user_khong_mk')) {
  function cap_nhat_user_khong_mk($id, $ten, $email, $vai_tro){
    global $ket_noi;
    $stm = $ket_noi->prepare("UPDATE users SET ten=:t, email=:e, vai_tro=:vr WHERE id=:id");
    return $stm->execute([':t'=>$ten, ':e'=>$email, ':vr'=>$vai_tro, ':id'=>$id]);
  }
}
if (!function_exists('xoa_user')) {
  function xoa_user($id){
    global $ket_noi;
    // Nếu có FK từ event_registrations.user_id -> users.id nên để ON DELETE SET NULL
    $stm = $ket_noi->prepare("DELETE FROM users WHERE id=:id");
    return $stm->execute([':id'=>$id]);
  }
}

/* ---------------- Router ---------------- */
$hanh_dong = $_GET['hanh_dong'] ?? '';
$id = (int)($_GET['id'] ?? 0);

$tb=''; $loi='';

try {
  // Xóa
  if ($hanh_dong === 'xoa' && $id>0) {
    if (!empty($_SESSION['user_id']) && $_SESSION['user_id']===$id) {
      $loi = 'Không thể tự xóa tài khoản đang đăng nhập.';
    } else {
      xoa_user($id);
      header('Location: '.$base.'/admin/index.php'); exit;
    }
  }

  // Lưu THÊM
  if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['hanh_dong'] ?? '')==='luu_them') {
    $ten = trim($_POST['ten'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mk = trim($_POST['mat_khau'] ?? '');
    $vai_tro = (int)($_POST['vai_tro'] ?? 2);
    if ($vai_tro!==1) $vai_tro = 2;

    if ($ten==='' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($mk)<4) {
      $loi = 'Dữ liệu không hợp lệ.';
    } elseif (tim_user_theo_email($email)) {
      $loi = 'Email đã tồn tại.';
    } else {
      tao_user($ten, $email, $mk, $vai_tro);
      header('Location: '.$base.'/admin/index.php'); exit;
    }
  }

  // Lưu SỬA (không đổi mật khẩu)
  if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['hanh_dong'] ?? '')==='luu_sua') {
    $id = (int)($_POST['id'] ?? 0);
    $ten = trim($_POST['ten'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $vai_tro = (int)($_POST['vai_tro'] ?? 2);
    if ($vai_tro!==1) $vai_tro = 2;

    if ($id<=0 || $ten==='' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $loi = 'Dữ liệu không hợp lệ.';
    } else {
      $uEmail = tim_user_theo_email($email);
      if ($uEmail && (int)$uEmail['id'] !== $id) {
        $loi = 'Email đã tồn tại ở user khác.';
      } else {
        cap_nhat_user_khong_mk($id, $ten, $email, $vai_tro);
        header('Location: '.$base.'/admin/index.php'); exit;
      }
    }
  }

} catch (Throwable $e) {
  $loi = $e->getMessage();
}

/* ---------------- View ---------------- */
$la_sua = ($hanh_dong === 'form_sua' && $id>0);
$u = $la_sua ? tim_user_theo_id($id) : ['id'=>0,'ten'=>'','email'=>'','vai_tro'=>2];
if ($la_sua && !$u) { $loi='User không tồn tại.'; $la_sua=false; }

include __DIR__ . '/../layout/header.php';
?>

<style>
  .card-auth{
    max-width: 640px;
    margin: 24px auto 40px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0,0,0,.08);
    padding: 28px;
  }
  .card-auth h2{
    margin: 0 0 16px;
    font-size: 22px;
    text-align:center;
  }
  .form-row{ display:flex; flex-direction:column; gap:6px; margin-bottom:14px; }
  .form-row label{ font-size:14px; color:#334155; }
  .form-row input, .form-row select{
    padding: 12px 14px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    font-size:15px;
    outline: none;
  }
  .form-row input:focus, .form-row select:focus{
    border-color:#60a5fa; box-shadow: 0 0 0 4px rgba(59,130,246,.15);
  }
  .actions{ display:flex; gap:10px; justify-content:center; margin-top: 14px; }
  .btn{
    border:0; cursor:pointer; padding:10px 16px; border-radius:10px; font-size:15px;
    transition:transform .05s ease; display:inline-flex; align-items:center; gap:8px;
  }
  .btn:active{ transform: translateY(1px); }
  .btn-primary{ background:#16a34a; color:#fff; }
  .btn-ghost{ background:#f1f5f9; color:#0f172a; }
  .badge-role{
    display:inline-block; padding:4px 8px; border-radius:999px; font-size:12px; font-weight:600;
  }
  .role-admin{ background:#fee2e2; color:#991b1b; }
  .role-user{ background:#e0f2fe; color:#075985; }
</style>

<div class="card-auth">
  <h2><?= $la_sua ? 'Sửa user' : 'Thêm user' ?></h2>

  <?php if ($la_sua): ?>
    <div style="text-align:center;margin-bottom:8px">
      Vai trò hiện tại:
      <?php if ($u['vai_tro']=='admin'): ?>
        <span class="badge-role role-admin">Admin</span>
      <?php else: ?>
        <span class="badge-role role-user">User</span>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <?php if ($loi): ?>
    <div class="nho" style="background:#fee2e2;color:#991b1b;padding:10px;border-radius:10px;margin-bottom:12px">
      <?= htmlspecialchars($loi) ?>
    </div>
  <?php endif; ?>

  <form method="post" action="">
    <input type="hidden" name="hanh_dong" value="<?= $la_sua ? 'luu_sua' : 'luu_them' ?>">
    <?php if ($la_sua): ?>
      <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
    <?php endif; ?>

    <div class="form-row">
      <label>Tên</label>
      <input type="text" name="ten" value="<?= htmlspecialchars($u['ten']) ?>" required>
    </div>

    <div class="form-row">
      <label>Email</label>
      <input type="email" name="email" value="<?= htmlspecialchars($u['email']) ?>" required>
    </div>

    <!-- Chỉ yêu cầu mật khẩu khi THÊM; SỬA thì không cho đổi mật khẩu -->
    <?php if (!$la_sua): ?>
      <div class="form-row">
        <label>Mật khẩu</label>
        <input type="password" name="mat_khau" minlength="4" placeholder="Tối thiểu 4 ký tự" required>
      </div>
    <?php endif; ?>
    <div class="form-row">
      <label>Vai trò</label>
      <select name="vai_tro">
        <option value="admin" <?= $u['vai_tro']=='admin'?'selected':'' ?>>Admin</option>
        <option value="user" <?= $u['vai_tro']=='user'?'selected':'' ?>>User</option>
      </select>
    </div>

    <div class="actions">
      <button class="btn btn-primary" type="submit">💾 <?= $la_sua ? 'Cập nhật' : 'Tạo user' ?></button>
      <a class="btn btn-ghost" href="<?= $base ?>/admin/index.php">↩ Quay lại</a>
    </div>
  </form>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
