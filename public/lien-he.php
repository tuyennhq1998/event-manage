<?php
require_once __DIR__ . '/../functions.php';

$tb = ''; $loi = '';

if (!empty($_POST)) {
  $ho_ten = trim($_POST['ho_ten'] ?? '');
  $email  = trim($_POST['email'] ?? '');
  $sdt    = trim($_POST['so_dien_thoai'] ?? '');
  $tieu_de= trim($_POST['tieu_de'] ?? '');
  $noi_dung = trim($_POST['noi_dung'] ?? '');

  if ($ho_ten && filter_var($email, FILTER_VALIDATE_EMAIL) && $tieu_de && $noi_dung){
    if (tao_lien_he($ho_ten,$email,$sdt,$tieu_de,$noi_dung)){
      // gửi email cho admin (nếu cấu hình)
      if (!empty($cfg_email_admin)) {
        $html = '<p>Có liên hệ mới:</p>'
              . '<p><b>'.$ho_ten.'</b> ('.$email.')'.($sdt? ' - '.$sdt:'').'</p>'
              . '<p><b>'.$tieu_de.'</b></p>'
              . '<div>'.nl2br(htmlspecialchars($noi_dung)).'</div>';
        @gui_email_don_gian($cfg_email_admin, '[Lien he moi] '.$tieu_de, $html);
      }
      $tb = 'Gửi liên hệ thành công! Chúng tôi sẽ phản hồi sớm.';
    } else { $loi = 'Không lưu được liên hệ. Vui lòng thử lại.'; }
  } else {
    $loi = 'Vui lòng nhập đầy đủ và đúng định dạng email.';
  }
}

include __DIR__ . '/../layout/header.php';
?>
<style>
.box-lien-he { max-width: 820px; margin: 0 auto; }
.box-lien-he .khung-form {
  background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:18px 20px;
  box-shadow:0 2px 6px rgba(0,0,0,.05);
}
.box-lien-he .nhom { display:flex; flex-direction:column; gap:6px; margin-bottom:12px; }
.box-lien-he label { font-weight:600; color:#334155; font-size:13px; }
.box-lien-he input, .box-lien-he textarea {
  border:1px solid #cbd5e1; border-radius:10px; padding:10px 12px; font-size:14px;
}
.box-lien-he textarea { min-height:160px; resize:vertical; }
.box-lien-he .nut { padding:10px 16px; border-radius:10px; background:#0ea5e9; color:#fff; border:0; cursor:pointer; font-weight:600; }
.box-lien-he .nut:hover { background:#0284c7; }
.alert-ok { background:#dcfce7; color:#166534; padding:10px 12px; border-radius:10px; margin-bottom:12px; }
.alert-err{ background:#fee2e2; color:#991b1b; padding:10px 12px; border-radius:10px; margin-bottom:12px; }
</style>

<div class="box-lien-he">
  <h2 style="text-align:center;margin-bottom:14px">Liên hệ</h2>
  <?php if ($tb): ?><div class="alert-ok"><?= htmlspecialchars($tb) ?></div><?php endif; ?>
  <?php if ($loi): ?><div class="alert-err"><?= htmlspecialchars($loi) ?></div><?php endif; ?>

  <form method="post" class="khung-form">
    <div class="nhom">
      <label>Họ tên</label>
      <input type="text" name="ho_ten" required value="<?= htmlspecialchars($_POST['ho_ten'] ?? '') ?>">
    </div>
    <div class="nhom" style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
      <div>
        <label>Email</label>
        <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div>
        <label>Số điện thoại</label>
        <input type="text" name="so_dien_thoai" value="<?= htmlspecialchars($_POST['so_dien_thoai'] ?? '') ?>">
      </div>
    </div>
    <div class="nhom">
      <label>Tiêu đề</label>
      <input type="text" name="tieu_de" required value="<?= htmlspecialchars($_POST['tieu_de'] ?? '') ?>">
    </div>
    <div class="nhom">
      <label>Nội dung</label>
      <textarea name="noi_dung" required><?= htmlspecialchars($_POST['noi_dung'] ?? '') ?></textarea>
    </div>
    <div style="display:flex;gap:10px;justify-content:flex-end">
      <button class="nut" type="submit">📨 Gửi liên hệ</button>
    </div>
  </form>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
