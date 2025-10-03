<?php
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../model/User.php';

$user = new User($ket_noi);
// Lay logo neu co (tu options) - dung ham get_option ban co san
if (!function_exists('get_option')) {
  function get_option($key)
  {
    global $ket_noi;
    $stm = $ket_noi->prepare("SELECT opt_value FROM options WHERE opt_key = :k");
    $stm->execute([':k' => $key]);
    return $stm->fetchColumn() ?: '';
  }
}
$logo_url = get_option('site_logo');

$tb = '';
$tb_loai = ''; // 'ok' | 'loi'
$ten_input = '';
$email_input = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $ten = trim($_POST['ten'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $mat_khau = trim($_POST['mat_khau'] ?? '');

  // giu lai input khi co loi
  $ten_input = $ten;
  $email_input = $email;

  if ($ten && filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($mat_khau) >= 4) {
    if (!$user->tim_user_theo_email($email)) {
      // GOI HAM tao_user KHONG dung named argument
      $id = $user->tao_user($ten, $email, $mat_khau);

      // gui email chao mung
      gui_email_don_gian(
        $email,
        'Chào mừng',
        '<p>Xin chào <b>' . htmlspecialchars($ten) . '</b>,</p>
         <p>Tài khoản của bạn đã được tạo thành công.</p>
         <p>Bạn có thể đăng nhập tại : <a href="' . htmlspecialchars($cfg_base_url) . '/public/dang_nhap.php">Đăng nhập</a></p>'
      );

      $tb = 'Đăng ký tài khoản thành công.';
      $tb_loai = 'ok';
      // reset form
      $ten_input = '';
      $email_input = '';
    } else {
      $tb = 'Email đã tồn tại.';
      $tb_loai = 'loi';
    }
  } else {
    $tb = 'Dữ liệu không hợp lệ (kiểm tra tên, định dạng email và mật khẩu >= 4 ký tự).';
    $tb_loai = 'loi';
  }
}

include __DIR__ . '/../layout/header.php';
?>
<style>
/* CSS cuc bo cho trang dang ky */
.khung-dk-tk {
    min-height: calc(100vh - 160px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
}

.the-dk-tk {
    width: 100%;
    max-width: 520px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 12px 36px rgba(0, 0, 0, .10);
    border: 1px solid rgba(0, 0, 0, .06);
    overflow: hidden;
}

.dk-header {
    padding: 20px 24px;
    background: linear-gradient(135deg, #22c55e 0%, #0ea5e9 100%);
    color: #fff;
    display: flex;
    align-items: center;
    gap: 12px;
}

.dk-header .logo {
    width: 44px;
    height: 44px;
    background: #ffffff22;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .25);
}

.dk-header .logo img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.dk-body {
    padding: 20px 24px 24px;
}

.form-nhom {
    margin-bottom: 12px;
}

.form-nhom label {
    font-weight: 600;
    display: block;
    margin-bottom: 6px;
}

.ip {
    width: 100%;
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #111;
    border-radius: 12px;
    padding: 12px 14px;
    font-size: 14px;
    outline: none;
}

.ip:focus {
    border-color: #93c5fd;
    box-shadow: 0 0 0 4px #dbeafe;
}

.loi-hop {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
    padding: 10px 12px;
    border-radius: 12px;
    margin-bottom: 12px;
}

.ok-hop {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
    padding: 10px 12px;
    border-radius: 12px;
    margin-bottom: 12px;
}

.nut-chinh-rong {
    width: 100%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 14px;
    border-radius: 12px;
    font-weight: 700;
    border: none;
    cursor: pointer;
    background: #111827;
    color: #fff;
}

.nut-chinh-rong:hover {
    background: #0f172a;
}

.nut-phu {
    color: #0ea5e9;
    text-decoration: none;
    font-weight: 600;
}

.nut-phu:hover {
    text-decoration: underline;
}

.nhac-nho {
    font-size: 12px;
    opacity: .75;
}
</style>

<div class="khung-dk-tk">
    <div class="the-dk-tk">
        <div class="dk-header">
            <div class="logo">
                <?php if (!empty($logo_url)): ?>
                <img src="<?= htmlspecialchars($logo_url) ?>" alt="Logo">
                <?php else: ?>
                <span style="font-weight:800;letter-spacing:.5px">LOGO</span>
                <?php endif; ?>
            </div>
            <div>
                <div style="font-size:18px;font-weight:800;line-height:1">Đăng ký tài khoản</div>
            </div>
        </div>

        <div class="dk-body">
            <?php if ($tb): ?>
            <div class="<?= $tb_loai === 'ok' ? 'ok-hop' : 'loi-hop' ?>">
                <?= htmlspecialchars($tb) ?>
            </div>
            <?php endif; ?>

            <form method="post" action="<?= htmlspecialchars($cfg_base_url) ?>/public/dang_ky_tai_khoan.php"
                autocomplete="on" novalidate>
                <div class="form-nhom">
                    <label>Họ và tên</label>
                    <input class="ip" type="text" name="ten" required placeholder="VD: Nguyen Van A"
                        value="<?= htmlspecialchars($ten_input) ?>">
                </div>

                <div class="form-nhom">
                    <label>Email</label>
                    <input class="ip" type="email" name="email" required placeholder="you@example.com"
                        value="<?= htmlspecialchars($email_input) ?>">
                </div>

                <div class="form-nhom">
                    <label>Mật khẩu</label>
                    <input class="ip" type="password" name="mat_khau" required placeholder=">= 4 ky tu">
                </div>

                <div class="form-nhom" style="margin-top:6px">
                    <button class="nut-chinh-rong" type="submit">📝 Tạo tài khoản</button>
                </div>

                <div class="form-nhom" style="margin-top:4px; text-align:center">
                    <span class="nhac-nho">Da co tai khoan?
                        <a class="nut-phu" href="<?= htmlspecialchars($cfg_base_url) ?>/public/dang_nhap.php">Đăng
                            nhập</a>
                    </span>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>