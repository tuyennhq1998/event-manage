<?php 
require_once __DIR__ . '/../functions.php'; 
require_once __DIR__ . '/../model/User.php';
$user = new User($ket_noi);

$user->bat_buoc_admin();

$base      = rtrim($cfg_base_url ?? '', '/'); 
$uploadDir = __DIR__ . '/../uploads'; 
$uploadUrl = $base . '/uploads'; 
if (!is_dir($uploadDir)) @mkdir($uploadDir, 0775, true);  

function opt_get($k){ 
    global $ket_noi;   
    $stm=$ket_noi->prepare("SELECT opt_value FROM options WHERE opt_key=:k");   
    $stm->execute([':k'=>$k]); 
    return $stm->fetchColumn() ?: ''; 
} 

function opt_set($k,$v){ 
    global $ket_noi;   
    $stm=$ket_noi->prepare("INSERT INTO options(opt_key,opt_value) VALUES(:k,:v)                           ON DUPLICATE KEY UPDATE opt_value=:v");   
    return $stm->execute([':k'=>$k,':v'=>$v]); 
}  

/* AJAX save */ 
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_GET['ajax'])) {   
    header('Content-Type: application/json; charset=utf-8');   
    try {     
        if (!is_writable($uploadDir)) throw new RuntimeException('uploads không ghi được');      
        
        $allow=['jpg','jpeg','png','webp','gif']; 
        $max=5*1024*1024;     
        
        // Function upload single file
        $up=function($field) use($allow,$max,$uploadDir,$uploadUrl){       
            if (!isset($_FILES[$field]) || $_FILES[$field]['error']!==UPLOAD_ERR_OK) return null;       
            $f=$_FILES[$field]; 
            if($f['size']>$max) throw new RuntimeException('File quá 5MB');       
            $ext=strtolower(pathinfo($f['name'],PATHINFO_EXTENSION));       
            if(!in_array($ext,$allow,true)) throw new RuntimeException('Định dạng sai');       
            $name='opt_'.$field.'_'.date('Ymd_His').'_'.bin2hex(random_bytes(3)).'.'.$ext;       
            if(!move_uploaded_file($f['tmp_name'],$uploadDir.'/'.$name)) throw new RuntimeException('Lưu file lỗi');       
            return $uploadUrl.'/'.$name;     
        };      
        
        // Function upload multiple files for banner
        $upMultiple=function($field) use($allow,$max,$uploadDir,$uploadUrl){
            if (!isset($_FILES[$field]) || !is_array($_FILES[$field]['name'])) return [];
            
            $uploadedFiles = [];
            $files = $_FILES[$field];
            $fileCount = count($files['name']);
            
            for ($i = 0; $i < $fileCount; $i++) {
                if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
                
                if ($files['size'][$i] > $max) throw new RuntimeException('File quá 5MB: ' . $files['name'][$i]);
                
                $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                if (!in_array($ext, $allow, true)) throw new RuntimeException('Định dạng sai: ' . $files['name'][$i]);
                
                $name = 'opt_banner_'.date('Ymd_His').'_'.bin2hex(random_bytes(3)).'.'.$ext;
                
                if (!move_uploaded_file($files['tmp_name'][$i], $uploadDir.'/'.$name)) {
                    throw new RuntimeException('Lưu file lỗi: ' . $files['name'][$i]);
                }
                
                $uploadedFiles[] = $uploadUrl.'/'.$name;
            }
            
            return $uploadedFiles;
        };
        
        // xoá nếu tick     
        if (!empty($_POST['xoa_logo']))   opt_set('site_logo','');     
        if (!empty($_POST['xoa_banner'])) opt_set('site_banner','');
        
        // upload logo (single file)
        if ($u=$up('logo'))   opt_set('site_logo',$u);     
        
        // xử lý banner
        $keepBanners = $_POST['keep_banners'] ?? '';
        $keepBannersArray = $keepBanners ? array_values(array_filter(array_map('trim', explode(',', $keepBanners)))) : [];
                        
        $newBanners = $upMultiple('banner');
        $allBanners = array_merge($keepBannersArray, $newBanners);
        $allBanners = array_filter($allBanners); // loại bỏ rỗng
        
        opt_set('site_banner', implode(',', $allBanners));
        
        echo json_encode([
            'ok'=>1,
            'logo'=>opt_get('site_logo'),
            'banner'=>opt_get('site_banner')
        ], JSON_UNESCAPED_UNICODE);   
    } catch(Throwable $e){     
        echo json_encode(['ok'=>0,'err'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);   
    }   
    exit; 
}  

/* GET: render form */ 
$logo   = opt_get('site_logo'); 
$banner = opt_get('site_banner'); 
$banners = $banner ? explode(',', $banner) : [];
?> 
<h3 style="margin:0 0 10px">Cài đặt (logo & banner)</h3>  

<form id="form-cai-dat" class="the"       
      action="<?= htmlspecialchars($base) ?>/admin/tab_cai_dat.php?ajax=1"       
      method="post" enctype="multipart/form-data">    
    
    <!-- Logo -->   
    <div class="form-nhom">     
        <h3>Logo</h3>     
        <div class="hang" style="gap:12px;align-items:flex-start">       
            <input class="chon-anh" type="file" name="logo" accept="image/*">       
            <img id="xem-logo" src="<?= htmlspecialchars($logo) ?>" alt="Logo" style="max-height:120px;border:1px solid #e5e7eb;padding:4px;border-radius:8px;background:#fff">     
        </div>   
    </div>    
    
    <!-- Banner -->   
    <div class="form-nhom" style="margin-top:14px">     
        <h3>Banner (Chọn nhiều ảnh)</h3>     
        <div class="hang" style="gap:12px;align-items:flex-start;flex-direction:column">       
            <input type="file" name="banner[]" accept="image/*" multiple>       
            
            <div id="banner-list" style="display:flex;gap:8px;flex-wrap:wrap">
                <?php foreach ($banners as $bannerUrl): ?>
                    <?php if (trim($bannerUrl)): ?>
                        <div class="banner-item" style="position:relative;">
                        <img
  src="<?= htmlspecialchars(trim($bannerUrl)) ?>"
  data-url="<?= htmlspecialchars(trim($bannerUrl)) ?>"
  alt="Banner"
  style="max-height:120px;border:1px solid #e5e7eb;padding:4px;border-radius:8px;background:#fff">
  <button type="button"
  onclick="(function(btn){
      if(!confirm('Xóa ảnh này?')) return;
      var item = btn.closest('.banner-item');
      if(item) item.remove();

      // rebuild keep_banners từ ảnh còn lại
      var keep = document.getElementById('keep_banners');
      if(!keep) return;
      var imgs = document.querySelectorAll('#banner-list .banner-item img');
      var urls = [];
      for (var i = 0; i < imgs.length; i++) {
        var u = (imgs[i].getAttribute('data-url') || imgs[i].src || '').trim();
        if (u) urls.push(u);
      }
      keep.value = urls.join(',');
  })(this);"
  style="position:absolute;top:-5px;right:-5px;background:red;color:white;border:none;border-radius:50%;width:20px;height:20px;cursor:pointer;font-size:12px">×</button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>   
    </div>    
    
    <input type="hidden" name="keep_banners" id="keep_banners" value="<?= htmlspecialchars($banner) ?>">
    
    <div style="margin-top:16px">     
        <button type="submit" class="nut chinh">💾 Lưu cài đặt</button>   
    </div> 
</form>

<script>
// Gán vào window để chắc chắn gọi được từ nút onclick
window.removeBanner = function(_url, button) {
    if (!confirm('Xóa ảnh này?')) return;

    const item = button.closest('.banner-item');
    if (item) item.remove();

    // Rebuild hidden input từ các ảnh còn lại
    const keepBannersInput = document.getElementById('keep_banners');
    if (!keepBannersInput) return;

    const urls = Array.from(document.querySelectorAll('#banner-list .banner-item img'))
        .map(img => (img.getAttribute('data-url') || img.src).trim()) // ưu tiên data-url
        .filter(Boolean);

    keepBannersInput.value = urls.join(',');
};

// Submit form bằng AJAX
document.getElementById('form-cai-dat').addEventListener('submit', function(e) {
    e.preventDefault();

    let formData = new FormData(this);
    let button = this.querySelector('button[type="submit"]');
    button.textContent = 'Đang lưu...';
    button.disabled = true;

    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            alert('Lưu thành công!');
            location.reload();
        } else {
            alert('Lỗi: ' + data.err);
        }
    })
    .catch(error => {
        alert('Có lỗi xảy ra!');
        console.error(error);
    })
    .finally(() => {
        button.textContent = '💾 Lưu cài đặt';
        button.disabled = false;
    });
});
</script>
