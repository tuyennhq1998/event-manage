<?php
require_once __DIR__ . '/../functions.php';

// ==== Lấy tham số lọc từ GET ====
$q        = trim($_GET['q'] ?? '');
$tu_ngay  = trim($_GET['tu_ngay'] ?? ''); // YYYY-MM-DD
$den_ngay = trim($_GET['den_ngay'] ?? ''); // YYYY-MM-DD

$tu_dt  = $tu_ngay  ? ($tu_ngay . ' 00:00:00') : null;
$den_dt = $den_ngay ? ($den_ngay . ' 23:59:59') : null;

// ==== Hàm load danh sách theo trạng thái + filter ====
function ds_theo_tt($tt, $limit=3, $offset=0, $q='', $tu_dt=null, $den_dt=null){
  global $ket_noi;
  $now = date('Y-m-d H:i:s');

  if ($tt === 'sap_toi') {
    $where = 'e.thoi_gian_bat_dau > :now';
    $order = ' ORDER BY e.thoi_gian_bat_dau ASC, e.id ASC ';     // 👈 thêm e.id
  } elseif ($tt === 'dang_dien_ra') {
    $where = 'e.thoi_gian_bat_dau <= :now AND e.thoi_gian_ket_thuc >= :now';
    $order = ' ORDER BY e.thoi_gian_bat_dau ASC, e.id ASC ';     // 👈 thêm e.id
  } else {
    $where = 'e.thoi_gian_ket_thuc < :now';
    $order = ' ORDER BY e.thoi_gian_bat_dau DESC, e.id DESC ';   // 👈 thêm e.id
  }
  
  $params = [':now' => $now];

  if ($q !== '') { $where .= ' AND e.tieu_de LIKE :kw'; $params[':kw'] = '%'.$q.'%'; }
  if ($tu_dt)     { $where .= ' AND e.thoi_gian_bat_dau >= :tu';  $params[':tu']  = $tu_dt; }
  if ($den_dt)    { $where .= ' AND e.thoi_gian_bat_dau <= :den'; $params[':den'] = $den_dt; }

  $sql = "SELECT e.* FROM events e WHERE $where $order LIMIT :lim OFFSET :off";
  $stm = $ket_noi->prepare($sql);
  foreach ($params as $k=>$v) $stm->bindValue($k,$v);
  $stm->bindValue(':lim',  (int)$limit,  PDO::PARAM_INT);
  $stm->bindValue(':off',  (int)$offset, PDO::PARAM_INT);
  $stm->execute();
  return $stm->fetchAll(PDO::FETCH_ASSOC);
}

function chip($tt){ return $tt==='sap_toi'?'Sắp tới':($tt==='dang_dien_ra'?'Đang diễn ra':'Đã kết thúc'); }

function render_card($sk,$chip_text){
  $link = htmlspecialchars($GLOBALS['cfg_base_url'].'/public/su_kien.php?id='.$sk['id']);
  $tieu_de = htmlspecialchars($sk['tieu_de']);
  $bg = trim((string)($sk['anh_bia'] ?? ''));

  if ($bg === '') {
    return '<a class="o-anh" href="'.$link.'" style="background:#ff8c00;display:flex;align-items:flex-end">'
         . '<div class="chip-nho">'.$chip_text.'</div>'
         . '<div class="tieu_de" style="color:#fff;padding:12px;font-weight:700">'.$tieu_de.'</div>'
         . '</a>';
  }
  $bg_css = "background-image:url('".htmlspecialchars($bg, ENT_QUOTES)."')";
  return '<a class="o-anh" href="'.$link.'">'
       .   '<div class="bg" style="'.$bg_css.'"></div>'
       .   '<div class="lop"></div>'
       .   '<div class="chip-nho">'.$chip_text.'</div>'
       .   '<div class="tieu_de">'.$tieu_de.'</div>'
       . '</a>';
}

$ds1 = ds_theo_tt('sap_toi',       3, 0, $q, $tu_dt, $den_dt);
$ds2 = ds_theo_tt('dang_dien_ra',  3, 0, $q, $tu_dt, $den_dt);
$ds3 = ds_theo_tt('da_ket_thuc',   3, 0, $q, $tu_dt, $den_dt);

include __DIR__ . '/../layout/header.php';
?>

<style>
.form-loc {
  display: grid;
  grid-template-columns: 40% 20% 20% 5%; /* search | từ | đến | nút */
  gap: 12px;
  align-items: end;
  background: #fff;
  padding: 14px 18px;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  margin-bottom: 20px;
  box-shadow: 0 2px 5px rgba(0,0,0,.05);
}
.form-loc .nhom { display:flex; flex-direction:column; gap:6px; }
.form-loc label { font-size:13px; font-weight:600; color:#334155; }
.form-loc input[type="date"], .form-loc input[type="search"]{
  padding:8px 10px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px;
}
.form-loc .nut{
  padding:10px 16px; border-radius:8px; border:0; font-weight:600;
  background:#0ea5e9; color:#fff; cursor:pointer; white-space:nowrap;
}
.form-loc .nut:hover{ background:#0284c7; }
@media (max-width:900px){ .form-loc{ grid-template-columns:1fr; } }
</style>

<div id="home-page" class="home-page" data-base-url="<?= htmlspecialchars($cfg_base_url) ?>">

  <!-- FILTER -->
  <form class="form-loc" method="get" action="">
    <div class="nhom">
      <label>Tìm theo tiêu đề</label>
      <input type="search" name="q" placeholder="Nhập tiêu đề sự kiện…" value="<?= htmlspecialchars($q) ?>">
    </div>
    <div class="nhom">
      <label>Từ ngày</label>
      <input type="date" name="tu_ngay" value="<?= htmlspecialchars($tu_ngay) ?>">
    </div>
    <div class="nhom">
      <label>Đến ngày</label>
      <input type="date" name="den_ngay" value="<?= htmlspecialchars($den_ngay) ?>">
    </div>
    <div class="nhom">
      <button class="nut" type="submit">🔎 Lọc</button>
    </div>
  </form>

  <!-- Tabs -->
  <div class="home-tabs">
    <button class="tab active" data-nhom="sap_toi">Sắp tới</button>
    <button class="tab" data-nhom="dang_dien_ra">Đang diễn ra</button>
    <button class="tab" data-nhom="da_ket_thuc">Đã kết thúc</button>
  </div>

  <!-- Pane: Sắp tới -->
  <section class="home-pane active" data-nhom="sap_toi">
    <h3 class="home-h3">Sắp tới</h3>
    <div class="home-grid" data-nhom="sap_toi">
      <?php foreach($ds1 as $sk) echo render_card($sk, chip('sap_toi')); if(empty($ds1)) echo '<p class="nho">Không có sự kiện.</p>';?>
    </div>
    <div class="home-see-more">
      <button class="btn-more"
        data-nhom="sap_toi"
        data-q="<?= htmlspecialchars($q) ?>"
        data-tu="<?= htmlspecialchars($tu_ngay) ?>"
        data-den="<?= htmlspecialchars($den_ngay) ?>">Xem thêm</button>
    </div>
  </section>

  <!-- Pane: Đang diễn ra -->
  <section class="home-pane" data-nhom="dang_dien_ra">
    <h3 class="home-h3">Đang diễn ra</h3>
    <div class="home-grid" data-nhom="dang_dien_ra">
      <?php foreach($ds2 as $sk) echo render_card($sk, chip('dang_dien_ra')); if(empty($ds2)) echo '<p class="nho">Không có sự kiện.</p>';?>
    </div>
    <div class="home-see-more">
      <button class="btn-more"
        data-nhom="dang_dien_ra"
        data-q="<?= htmlspecialchars($q) ?>"
        data-tu="<?= htmlspecialchars($tu_ngay) ?>"
        data-den="<?= htmlspecialchars($den_ngay) ?>">Xem thêm</button>
    </div>
  </section>

  <!-- Pane: Đã kết thúc -->
  <section class="home-pane" data-nhom="da_ket_thuc">
    <h3 class="home-h3">Đã kết thúc</h3>
    <div class="home-grid" data-nhom="da_ket_thuc">
      <?php foreach($ds3 as $sk) echo render_card($sk, chip('da_ket_thuc')); if(empty($ds3)) echo '<p class="nho">Không có sự kiện.</p>';?>
    </div>
    <div class="home-see-more">
      <button class="btn-more"
        data-nhom="da_ket_thuc"
        data-q="<?= htmlspecialchars($q) ?>"
        data-tu="<?= htmlspecialchars($tu_ngay) ?>"
        data-den="<?= htmlspecialchars($den_ngay) ?>">Xem thêm</button>
    </div>
  </section>

</div>


<?php include __DIR__ . '/../layout/footer.php'; ?>
