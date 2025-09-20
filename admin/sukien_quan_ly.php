<?php
require_once __DIR__ . '/../functions.php';
bat_buoc_admin();

$hanh_dong = $_POST['hanh_dong'] ?? $_GET['hanh_dong'] ?? '';

if ($hanh_dong === 'them' && !empty($_POST)) {
  // mo_ta: bản text ngắn để fallback tìm kiếm; rút từ html
  $mo_ta_html = $_POST['mo_ta_html'] ?? '';
  $mo_ta_text = trim(strip_tags($mo_ta_html));
  them_su_kien(
    $_POST['tieu_de'], $mo_ta_text, $_POST['dia_diem'],
    $_POST['thoi_gian_bat_dau'], $_POST['thoi_gian_ket_thuc'],
    $mo_ta_html, $_POST['anh_bia'] ?? null
  );
  header('Location: '.$_SERVER['PHP_SELF']); exit;
}

if ($hanh_dong === 'sua' && !empty($_POST)) {
  $mo_ta_html = $_POST['mo_ta_html'] ?? '';
  $mo_ta_text = trim(strip_tags($mo_ta_html));
  cap_nhat_su_kien(
    (int)$_POST['id'], $_POST['tieu_de'], $mo_ta_text, $_POST['dia_diem'],
    $_POST['thoi_gian_bat_dau'], $_POST['thoi_gian_ket_thuc'],
    $mo_ta_html, $_POST['anh_bia'] ?? null
  );
  header('Location: '.$_SERVER['PHP_SELF']); exit;
}

if ($hanh_dong === 'xoa' && isset($_GET['id'])) {
  xoa_su_kien((int)$_GET['id']);
  header('Location: '.$_SERVER['PHP_SELF']); exit;
}

$sua = null;
if (($hanh_dong === 'form_sua') && isset($_GET['id'])) {
  $sua = lay_su_kien_theo_id((int)$_GET['id']);
}

function dt_local($s){ return $s ? date('Y-m-d\TH:i', strtotime($s)) : ''; }

include __DIR__ . '/../layout/header.php';
?>

<h2 style="text-align:center"><?= $sua ? 'Sửa sự kiện' : 'Thêm sự kiện' ?></h2>

<div class="form-card">
  <form method="post" class="form-grid">
    <?php if ($sua): ?><input type="hidden" name="id" value="<?= $sua['id'] ?>"><?php endif; ?>
    <input type="hidden" name="hanh_dong" value="<?= $sua ? 'sua' : 'them' ?>">

    <div class="field cot-full">
      <label>Tiêu đề</label>
      <input type="text" name="tieu_de" required value="<?= htmlspecialchars($sua['tieu_de'] ?? '') ?>" style="
    width: 95%;
">
    </div>

    <div class="field cot-full">
      <label>Địa điểm</label>
      <input type="text" name="dia_diem" required value="<?= htmlspecialchars($sua['dia_diem'] ?? '') ?>" style="
    width: 95%;
">
    </div>

    <div class="field">
      <label>Thời gian bắt đầu</label>
      <input type="datetime-local" name="thoi_gian_bat_dau" required value="<?= dt_local($sua['thoi_gian_bat_dau'] ?? '') ?>">
    </div>

    <div class="field">
      <label>Thời gian kết thúc</label>
      <input type="datetime-local" name="thoi_gian_ket_thuc" required value="<?= dt_local($sua['thoi_gian_ket_thuc'] ?? '') ?>">
    </div>

<!-- Ảnh bìa: upload file + xem trước + hidden để giữ URL -->
<div class="field cot-full">
  <label>Ảnh bìa</label>
  <input type="file" id="file_anh_bia" accept="image/*">
  <input type="hidden" name="anh_bia" id="anh_bia" value="<?= htmlspecialchars($sua['anh_bia'] ?? '') ?>">
  <div class="preview-anh-bia" id="preview_anh_bia">
    <?php if (!empty($sua['anh_bia'])): ?>
      <img src="<?= htmlspecialchars($sua['anh_bia']) ?>" alt="Anh bia">
    <?php else: ?>
      <div class="nhac">Chưa có ảnh — chọn file để tải lên</div>
    <?php endif; ?>
  </div>
  <div class="status-upload" id="status_upload"></div>
</div>

    <div class="field cot-full">
      <label>Mô tả chi tiết (WYSIWYG)</label>
      <textarea id="mo_ta_html" name="mo_ta_html"><?= htmlspecialchars($sua['mo_ta_html'] ?? '') ?></textarea>
    </div>

    <div class="cot-full form-actions">
      <?php if ($sua): ?>
        <a class="nut phu" href="<?= $cfg_base_url ?>/admin/sukien_quan_ly.php">Hủy</a>
        <button class="nut chinh" type="submit">💾 Lưu</button>
      <?php else: ?>
        <button class="nut phu" type="reset">✖ Xóa form</button>
        <button class="nut chinh" type="submit">➕ Thêm sự kiện</button>
      <?php endif; ?>
    </div>
  </form>
</div>

<!-- TinyMCE CDN + cấu hình upload -->
<script src="https://cdn.tiny.cloud/1/l02o6fkr26gk21ln9ffowkkbb5i9mfa6zttkec9qswu7yysw/tinymce/8/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>
<script>
tinymce.init({
  selector: '#mo_ta_html',
  height: 420,
  menubar: false,
  plugins: 'link lists image media table code paste autoresize',
  toolbar: 'undo redo | bold italic underline | forecolor backcolor | alignleft aligncenter alignright | bullist numlist outdent indent | link image media table | removeformat | code',
  paste_data_images: true,                  // cho dán ảnh từ clipboard
  images_upload_url: '<?= $cfg_base_url ?>/admin/upload_anh.php',
  images_upload_credentials: true,          // gửi cookie session
  automatic_uploads: true,
  convert_urls: false,                      // giữ nguyên URL ảnh
});
</script>
<script>
(function(){
  const input = document.getElementById('file_anh_bia');
  if (!input) return;

  const preview = document.getElementById('preview_anh_bia');
  const hidden  = document.getElementById('anh_bia');
  const status  = document.getElementById('status_upload');
  const UP_URL  = '<?= $cfg_base_url ?>/admin/upload_anh.php';

  function setStatus(msg, cls){ status.textContent = msg; status.className = 'status-upload ' + (cls||''); }

  input.addEventListener('change', async function(){
    const f = this.files && this.files[0];
    if (!f) return;

    // Xem trước tạm thời
    const urlTam = URL.createObjectURL(f);
    preview.innerHTML = '<img src="'+urlTam+'" alt="preview">';
    setStatus('Đang tải ảnh lên...', '');

    // Kiểm tra size (tuỳ chọn: 5MB)
    if (f.size > 5*1024*1024){ setStatus('File quá lớn (>5MB).', 'err'); return; }

    // Upload lên server
    try{
      const fd = new FormData(); fd.append('file', f);
      const res = await fetch(UP_URL, { method:'POST', body: fd, credentials:'same-origin' });
      const data = await res.json();
      if (!res.ok || !data.location) throw new Error(data.error || 'Upload thất bại');

      // Lưu URL vào input hidden để submit cùng form
      hidden.value = data.location;
      // Đổi preview sang URL chính thức (khỏi dùng objectURL)
      preview.innerHTML = '<img src="'+data.location+'" alt="Anh bia">';
      setStatus('✅ Ảnh đã tải lên & sẵn sàng lưu.', 'ok');
    }catch(e){
      console.error(e);
      setStatus('Lỗi upload: ' + e.message, 'err');
    }
  });
})();
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
