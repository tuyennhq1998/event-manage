<?php
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../model/DichVu.php';
$dv = new SuKien($ket_noi);

include __DIR__ . '/../layout/header.php';

$id = (int)($_GET['id'] ?? 0);
$su_kien = $dv->lay_su_kien_theo_id($id);
if (!$su_kien) {
  echo '<p>❌ Khong tim thay su kien.</p>';
  include __DIR__ . '/../layout/footer.php'; exit;
}

/* ====== TÍNH TRẠNG THÁI ====== */
$tt = $dv->tinh_trang_thai_su_kien($su_kien['thoi_gian_bat_dau'], $su_kien['thoi_gian_ket_thuc']);
$ten_tt = [
  'sap_toi' => 'Sắp tới',
  'dang_dien_ra' => 'Đang diễn ra',
  'da_ket_thuc' => 'Đã kết thúc'
][$tt] ?? $tt;

/* ====== LẤY GIÁ & GIỚI HẠN, ĐẾM SỐ NGƯỜI ĐÃ ĐĂNG KÝ ====== */
$gia = (int)($su_kien['gia'] ?? 0);              // cột int/decimal trong DB
$gioi_han = (int)($su_kien['so_luong'] ?? 0);     // 0 hoặc NULL xem như không giới hạn

// Đếm số người đã đăng ký
$stm = $ket_noi->prepare("SELECT COUNT(*) FROM event_registrations WHERE su_kien_id = :id");
$stm->execute([':id' => $id]);
$so_da_dk = (int)$stm->fetchColumn();

// Tính còn lại nếu có giới hạn
$con_lai = ($gioi_han > 0) ? max(0, $gioi_han - $so_da_dk) : null;

// Format tiền VND
function format_vnd($n){ return $n > 0 ? number_format($n, 0, ',', '.') . ' đ' : 'Miễn phí'; }
$hien_gia = format_vnd($gia);

/* ====== KIỂM TRA USER ĐÃ ĐĂNG KÝ CHƯA ====== */
$da_dang_ky = false;
if (isset($_SESSION['user_id'])) {
    // Nếu có hệ thống đăng nhập với user_id
    $stm_check = $ket_noi->prepare("SELECT COUNT(*) FROM event_registrations WHERE su_kien_id = :su_kien_id AND user_id = :user_id");
    $stm_check->execute([
        ':su_kien_id' => $id,
        ':user_id' => $_SESSION['user_id']
    ]);
    $da_dang_ky = (int)$stm_check->fetchColumn() > 0;
} elseif (isset($_SESSION['user_email'])) {
    // Nếu chỉ có email trong session
    $stm_check = $ket_noi->prepare("SELECT COUNT(*) FROM event_registrations WHERE su_kien_id = :su_kien_id AND email = :email");
    $stm_check->execute([
        ':su_kien_id' => $id,
        ':email' => $_SESSION['user_email']
    ]);
    $da_dang_ky = (int)$stm_check->fetchColumn() > 0;
} elseif (isset($_COOKIE['user_email'])) {
    // Hoặc kiểm tra qua cookie nếu có
    $stm_check = $ket_noi->prepare("SELECT COUNT(*) FROM event_registrations WHERE su_kien_id = :su_kien_id AND email = :email");
    $stm_check->execute([
        ':su_kien_id' => $id,
        ':email' => $_COOKIE['user_email']
    ]);
    $da_dang_ky = (int)$stm_check->fetchColumn() > 0;
}

// Quyết định cho phép bấm đăng ký
$cho_phep_dk = ($tt === 'sap_toi') && ($gioi_han <= 0 || $con_lai > 0) && !$da_dang_ky;
?>

<!-- Banner đỏ -->
<div class="banner-do">
  <?php $banner_su_kien = $su_kien['anh_bia'] ? $su_kien['anh_bia'] : $cfg_base_url.'/uploads/banner/banner-su-kien-8.jpg'; ?>
  <img src="<?= htmlspecialchars($banner_su_kien) ?>" alt="Anh bia" style="height:300px">
</div>

<div class="chi-tiet-su-kien-header" style="text-align:center">
  <h1><?= htmlspecialchars($su_kien['tieu_de']) ?></h1>
  <div class="nho">
    🕒 <?= htmlspecialchars($su_kien['thoi_gian_bat_dau']) ?> → <?= htmlspecialchars($su_kien['thoi_gian_ket_thuc']) ?>
    &nbsp; | &nbsp;
    📍 <b><?= htmlspecialchars($su_kien['dia_diem']) ?></b>
  </div>

  <!-- Nhóm chip/trạng thái/giá/số lượng -->
  <div class="trang-thai" style="margin-top:10px; display:flex; gap:10px; align-items:center; justify-content:center; flex-wrap:wrap">
    <!-- Chip giá -->
    <span class="chip" style="background:#e0f2fe;color:#075985;">💰 <?= htmlspecialchars($hien_gia) ?></span>

    <!-- Chip số lượng -->
    <?php if ($gioi_han > 0): ?>
      <span class="chip" style="background:#f3e8ff;color:#6b21a8;">👥 <?= $so_da_dk ?> / <?= $gioi_han ?></span>
    <?php else: ?>
      <span class="chip" style="background:#f1f5f9;color:#0f172a;">👥 <?= $so_da_dk ?> người đã đăng ký </span>
    <?php endif; ?>

    <!-- Trạng thái / nút -->
    <?php if ($da_dang_ky): ?>
      <span class="chip" style="background:#dcfce7;color:#166534;">✅ Đã đăng ký</span>
    <?php elseif ($cho_phep_dk): ?>
      <button class="nut chinh" data-mo-popup data-su-kien-id="<?= $su_kien['id'] ?>">✅ Đăng ký tham gia</button>
    <?php else: ?>
      <?php if ($tt === 'sap_toi' && $gioi_han > 0 && $con_lai === 0): ?>
        <span class="chip daketthuc" style="background:#fee2e2;color:#991b1b;">Đã đủ chỗ</span>
      <?php elseif ($tt === 'dang_dien_ra'): ?>
        <span class="chip dangdienra">Đang diễn ra</span>
      <?php else: ?>
        <span class="chip daketthuc">Đã kết thúc</span>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<div class="the">
  <?= $su_kien['mo_ta_html'] ?: nl2br(htmlspecialchars($su_kien['mo_ta'])) ?>
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
      <button class="nut chinh" type="submit" <?= $cho_phep_dk ? '' : 'disabled' ?>>📩 Gửi đăng ký</button>
    </form>
  </div>
</div>
<script>
(function(){
  const form = document.getElementById('form_dang_ky_su_kien');
  if(!form) return;

  // Kiểm tra đã có listener chưa để tránh đăng ký nhiều lần
  if(form.dataset.listenerAdded === 'true') return;
  form.dataset.listenerAdded = 'true';

  let dangGui = false; // cờ chống double submit

  form.addEventListener('submit', async (e)=>{
    e.preventDefault();
    e.stopPropagation(); // Ngăn event bubble lên parent
    e.stopImmediatePropagation(); // Ngăn các handler khác trên cùng element

    const btn = form.querySelector('button[type="submit"]');
    
    // Kiểm tra đã đang gửi hoặc button đã disabled
    if (dangGui || (btn && btn.disabled)) {
      console.log('Đang xử lý, vui lòng chờ...');
      return false;
    }

    dangGui = true; // Khóa ngay lập tức
    
    if (btn){ 
      btn.disabled = true; 
      btn.classList.add('loading');
      btn.textContent = '⏳ Đang gửi...'; // Feedback trực quan
    }

    try{
      const data = new FormData(form);
      const res  = await fetch(form.action, { 
        method:'POST', 
        body:data, 
        credentials:'same-origin',
        headers: {
          'X-Requested-With': 'XMLHttpRequest' // Đánh dấu là AJAX request
        }
      });
      
      if(!res.ok) throw new Error('Network response was not ok');
      
      const txt  = await res.text();
      const kq   = JSON.parse(txt);

      if (kq.thanh_cong){
        alert(kq.thong_bao || 'Đăng ký thành công!');
        form.reset();
        
        // Đóng popup
        const popup = document.querySelector('.popup_nen');
        if (popup) popup.style.display = 'none';
        
        // Reload trang sau 1s để cập nhật trạng thái
        setTimeout(() => window.location.reload(), 1000);
        
        return false; // GIỮ dangGui=true, không cho submit lại
      } else {
        alert(kq.thong_bao || 'Có lỗi xảy ra!');
        // Cho phép thử lại khi có lỗi logic
        dangGui = false;
        if (btn){ 
          btn.disabled = false; 
          btn.classList.remove('loading');
          btn.textContent = '📩 Gửi đăng ký';
        }
      }
    } catch(err){
      console.error('Lỗi:', err);
      alert('Lỗi kết nối: ' + err.message);
      // Cho phép thử lại khi lỗi mạng/parse
      dangGui = false;
      if (btn){ 
        btn.disabled = false; 
        btn.classList.remove('loading');
        btn.textContent = '📩 Gửi đăng ký';
      }
    }

    return false; // Đảm bảo không submit form theo cách thông thường
  }, { passive:false, once:false }); // Bỏ once:true vì cần handle nhiều lần nếu có lỗi

  // Thêm validation trước khi submit
  form.addEventListener('invalid', (e) => {
    e.preventDefault();
    dangGui = false; // Reset nếu form invalid
  }, true);

})();
</script><?php include __DIR__ . '/../layout/footer.php'; ?>