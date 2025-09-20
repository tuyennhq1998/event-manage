<?php
require_once __DIR__ . '/../functions.php';
bat_buoc_admin();

$base      = rtrim($cfg_base_url ?? '', '/');
$uploadDir = __DIR__ . '/../uploads';
$uploadUrl = $base . '/uploads';
if (!is_dir($uploadDir)) @mkdir($uploadDir, 0775, true);

function opt_get($k){ global $ket_noi;
  $stm=$ket_noi->prepare("SELECT opt_value FROM options WHERE opt_key=:k");
  $stm->execute([':k'=>$k]); return $stm->fetchColumn() ?: '';
}
function opt_set($k,$v){ global $ket_noi;
  $stm=$ket_noi->prepare("INSERT INTO options(opt_key,opt_value) VALUES(:k,:v)
                          ON DUPLICATE KEY UPDATE opt_value=:v");
  return $stm->execute([':k'=>$k,':v'=>$v]);
}

/* AJAX save (giống sự kiện: post form -> lưu file -> trả JSON) */
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_GET['ajax'])) {
  header('Content-Type: application/json; charset=utf-8');
  try {
    if (!is_writable($uploadDir)) throw new RuntimeException('uploads không ghi được');

    $allow=['jpg','jpeg','png','webp','gif']; $max=5*1024*1024;
    $up=function($field) use($allow,$max,$uploadDir,$uploadUrl){
      if (!isset($_FILES[$field]) || $_FILES[$field]['error']!==UPLOAD_ERR_OK) return null;
      $f=$_FILES[$field]; if($f['size']>$max) throw new RuntimeException('File quá 5MB');
      $ext=strtolower(pathinfo($f['name'],PATHINFO_EXTENSION));
      if(!in_array($ext,$allow,true)) throw new RuntimeException('Định dạng sai');
      $name='opt_'.$field.'_'.date('Ymd_His').'_'.bin2hex(random_bytes(3)).'.'.$ext;
      if(!move_uploaded_file($f['tmp_name'],$uploadDir.'/'.$name)) throw new RuntimeException('Lưu file lỗi');
      return $uploadUrl.'/'.$name;
    };

    // xoá nếu tick
    if (!empty($_POST['xoa_logo']))   opt_set('site_logo','');
    if (!empty($_POST['xoa_banner'])) opt_set('site_banner','');

    // upload nếu có
    if ($u=$up('logo'))   opt_set('site_logo',$u);
    if ($u=$up('banner')) opt_set('site_banner',$u);

    echo json_encode(['ok'=>1,'logo'=>opt_get('site_logo'),'banner'=>opt_get('site_banner')], JSON_UNESCAPED_UNICODE);
  } catch(Throwable $e){
    echo json_encode(['ok'=>0,'err'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
  }
  exit;
}

/* GET: render form (giống form sự kiện: file + ảnh xem trước) */
$logo   = opt_get('site_logo');
$banner = opt_get('site_banner');
?>
<h3 style="margin:0 0 10px">Cài đặt (logo & banner)</h3>

<form id="form-cai-dat" class="the"
      action="<?= htmlspecialchars($base) ?>/admin/tab_cai_dat.php?ajax=1"
      method="post" enctype="multipart/form-data">

  <!-- Logo -->
  <div class="form-nhom">
    <h3>Logo</h3>
    <div class="hang" style="gap:12px;align-items:flex-start">
      <input class="chon-anh" type="file" name="logo" accept="image/*" data-preview="#xem-logo">
      <img id="xem-logo" src="<?= htmlspecialchars($logo) ?>" alt="Logo" style="max-height:120px;border:1px solid #e5e7eb;padding:4px;border-radius:8px;background:#fff">
    </div>
  </div>

  <!-- Banner -->
  <div class="form-nhom" style="margin-top:14px">
    <h3>Banner</h3>
    <div class="hang" style="gap:12px;align-items:flex-start">
      <input class="chon-anh" type="file" name="banner" accept="image/*" data-preview="#xem-banner">
      <img id="xem-banner" src="<?= htmlspecialchars($banner) ?>" alt="Banner" style="max-height:180px;border:1px solid #e5e7eb;padding:4px;border-radius:8px;background:#fff">
    </div>
  </div>

  <div style="margin-top:16px">
    <button id="btn-save-settings" class="nut chinh" type="submit">💾 Lưu cài đặt</button>
  </div>
</form>
