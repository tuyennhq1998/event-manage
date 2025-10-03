<?php
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../model/User.php';

$user = new User($ket_noi);

// Lấy logo nếu có (từ bảng options)
if (!function_exists('get_option')) {
  function get_option($key) {
    global $ket_noi;
    $stm = $ket_noi->prepare("SELECT opt_value FROM options WHERE opt_key = :k");
    $stm->execute([':k'=>$key]);
    return $stm->fetchColumn() ?: '';
  }
}
$logo_url = get_option('site_logo');

$loi = '';
$email_input = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $mat_khau = trim($_POST['mat_khau'] ?? '');
  $email_input = $email; // giữ lại email khi lỗi

  if ($user->dang_nhap($email, $mat_khau)) {
    header('Location: ' . $cfg_base_url . '/public/index.php');
    exit;
  } else {
    $loi = 'Email hoặc mật khẩu không đúng.';
  }
}

include __DIR__ . '/../layout/header.php';
?>
<style>
  /* Chỉ style cục bộ cho trang đăng nhập */
  .khung-dang-nhap {
    min-height: calc(100vh - 160px);
    display:flex; align-items:center; justify-content:center; padding:24px;
  }
  .the-dang-nhap {
    width: 100%; max-width: 460px;
    background: #fff; border-radius: 16px;
    box-shadow: 0 12px 36px rgba(0,0,0,.10);
    border: 1px solid rgba(0,0,0,.06);
    overflow: hidden;
  }
  .dangnhap-header {
    padding: 20px 24px;
    background: linear-gradient(135deg, #0ea5e9 0%, #7c3aed 100%);
    color:#fff; display:flex; align-items:center; gap:12px;
  }
  .dangnhap-header .logo {
    width:44px; height:44px; background:#ffffff22; border-radius:12px;
    display:flex; align-items:center; justify-content:center; overflow:hidden;
    box-shadow: inset 0 0 0 1px rgba(255,255,255,.25);
  }
  .dangnhap-header .logo img { max-width:100%; max-height:100%; object-fit:contain; }
  .dangnhap-body { padding: 20px 24px 24px; }
  .form-nhom { margin-bottom:12px; }
  .form-nhom label { font-weight:600; display:block; margin-bottom:6px; }
  .form-nhom .ip {
    width:100%; border:1px solid #e5e7eb; background:#fff; color:#111;
    border-radius:12px; padding:12px 14px; font-size:14px;
    outline:none;
  }
  .form-nhom .ip:focus { border-color:#93c5fd; box-shadow:0 0 0 4px #dbeafe; }
  .hang-giua { display:flex; align-items:center; justify-content:space-between; gap:8px; }
  .loi-hop {
    background:#fee2e2; color:#991b1b; border:1px solid #fecaca;
    padding:10px 12px; border-radius:12px; margin-bottom:12px;
  }
  .nut-chinh-rong {
    width:100%; display:inline-flex; align-items:center; justify-content:center; gap:8px;
    padding:12px 14px; border-radius:12px; font-weight:700; border:none; cursor:pointer;
    background:#111827; color:#fff;
  }
  .nut-chinh-rong:hover { background:#0f172a; }
  .nut-phu { color:#0ea5e9; text-decoration:none; font-weight:600; }
  .nut-phu:hover { text-decoration:underline; }
  .nhac-nho { font-size:12px; opacity:.75; }
  .toggle-pass {
    position:absolute; right:10px; top:50%; transform:translateY(-50%);
    cursor:pointer; font-size:13px; color:#64748b;
    padding:4px 6px; border-radius:8px; background:#f1f5f9;
  }
  .ip-wrap { position:relative; }
</style>

<div class="khung-dang-nhap">
  <div class="the-dang-nhap">
    <div class="dangnhap-header">
     
      <div>
        <div style="font-size:18px;font-weight:800;line-height:1">Chào mừng trở lại</div>
        <div class="nhac-nho" style="color:#dbeafe">Đăng nhập để tiếp tục</div>
      </div>
    </div>

    <div class="dangnhap-body">
      <?php if ($loi): ?>
        <div class="loi-hop">⚠️ <?= htmlspecialchars($loi) ?></div>
      <?php endif; ?>

      <form method="post" autocomplete="on" novalidate>
        <div class="form-nhom">
          <label>Email</label>
          <input class="ip" type="email" name="email" placeholder="you@example.com" required
                 value="<?= htmlspecialchars($email_input) ?>">
        </div>

        <div class="form-nhom">
          <label>Mật khẩu</label>
          <div class="ip-wrap">
            <input class="ip" type="password" name="mat_khau" id="ip-mk" placeholder="••••••" required>
            <span class="toggle-pass" id="btn-toggle">Hiện</span>
          </div>
        </div>

        <div class="form-nhom hang-giua" style="margin-top:6px">
          <span class="nhac-nho">Chưa có tài khoản?
            <a class="nut-phu" href="<?= $cfg_base_url ?>/public/dang_ky_tai_khoan.php">Đăng ký</a>
          </span>
          <span class="nhac-nho"><!-- để trống hoặc thêm “Quên mật khẩu?” nếu có --></span>
        </div>

        <div class="form-nhom" style="margin-top:8px">
          <button class="nut-chinh-rong" type="submit">🔓 Đăng nhập</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// hiện/ẩn mật khẩu
(function(){
  const ip = document.getElementById('ip-mk');
  const btn = document.getElementById('btn-toggle');
  if (ip && btn) {
    btn.addEventListener('click', ()=>{
      const isPass = ip.type === 'password';
      ip.type = isPass ? 'text' : 'password';
      btn.textContent = isPass ? 'Ẩn' : 'Hiện';
    });
  }
})();
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
