<?php
require_once __DIR__ . '/../functions.php';
include __DIR__ . '/../layout/header.php';

$id = (int)($_GET['id'] ?? 0);
$su_kien = lay_su_kien_theo_id($id);
if (!$su_kien) {
  echo '<p>❌ Khong tim thay su kien.</p>';
  include __DIR__ . '/../layout/footer.php'; exit;
}

$tt = tinh_trang_thai_su_kien($su_kien['thoi_gian_bat_dau'], $su_kien['thoi_gian_ket_thuc']);

// map ten hien thi trang thai
$ten_tt = [
  'sap_toi' => 'Sắp tới',
  'dang_dien_ra' => 'Đang diễn ra',
  'da_ket_thuc' => 'Đã kết thúc'
][$tt] ?? $tt;
?>

<!-- Banner đỏ -->
<div class="banner-do">
  <h1>Chi tiết sự kiện</h1>
</div>

<div class="chi-tiet-su-kien-header">
  <h1><?= htmlspecialchars($su_kien['tieu_de']) ?></h1>
  <div class="nho">
    📍 <b><?= htmlspecialchars($su_kien['dia_diem']) ?></b>
    &nbsp; | &nbsp;
    🕒 <?= htmlspecialchars($su_kien['thoi_gian_bat_dau']) ?> → <?= htmlspecialchars($su_kien['thoi_gian_ket_thuc']) ?>
  </div>
  <div class="trang-thai">
    <?php if ($tt === 'sap_toi'): ?>
      <button class="nut chinh" data-mo-popup data-su-kien-id="<?= $su_kien['id'] ?>">✅ Đăng ký tham gia</button>
    <?php elseif ($tt === 'dang_dien_ra'): ?>
      <span class="chip dangdienra">Đang diễn ra</span>
    <?php else: ?>
      <span class="chip daketthuc">Đã kết thúc</span>
    <?php endif; ?>
  </div>
</div>

<div class="the">
  <?= $su_kien['mo_ta_html'] ?: nl2br(htmlspecialchars($su_kien['mo_ta'])) ?>
</div>

</div>

<!-- Popup đăng ký sự kiện -->
<div class="popup_nen">
  <div class="popup_hop">
    <div class="hang">
      <h3>Đăng ký tham gia sự kiện</h3>
      <button class="nut" data-dong-popup>✖</button>
    </div>
    <form id="form_dang_ky_su_kien" action="<?= $cfg_base_url ?>/public/dang_ky.php" method="post">
      <input type="hidden" name="su_kien_id" value="<?= $su_kien['id'] ?>">
      <label>Họ tên</label>
      <input type="text" name="ho_ten" required>
      <label>Email</label>
      <input type="email" name="email" required>
      <label>Số điện thoại</label>
      <input type="text" name="so_dien_thoai">
      <button class="nut chinh" type="submit">📩 Gửi đăng ký</button>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
