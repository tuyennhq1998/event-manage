<?php
require_once __DIR__ . '/../functions.php';
$banner = get_option('site_banner');

function ds_theo_tt($tt,$limit=3,$offset=0){
  global $ket_noi; $now=date('Y-m-d H:i:s');
  if ($tt==='sap_toi'){ $where='thoi_gian_bat_dau > :now'; $order=' ORDER BY thoi_gian_bat_dau ASC '; }
  elseif($tt==='dang_dien_ra'){ $where='thoi_gian_bat_dau<=:now AND thoi_gian_ket_thuc>=:now'; $order=' ORDER BY thoi_gian_bat_dau ASC '; }
  else{ $where='thoi_gian_ket_thuc < :now'; $order=' ORDER BY thoi_gian_bat_dau DESC '; }
  $sql="SELECT * FROM events WHERE $where $order LIMIT :lim OFFSET :off";
  $stm=$ket_noi->prepare($sql); $stm->bindValue(':now',$now); $stm->bindValue(':lim',$limit,PDO::PARAM_INT); $stm->bindValue(':off',$offset,PDO::PARAM_INT); $stm->execute();
  return $stm->fetchAll(PDO::FETCH_ASSOC);
}
function chip($tt){ return $tt==='sap_toi'?'Sắp tới':($tt==='dang_dien_ra'?'Đang diễn ra':'Đã kết thúc'); }
function render_card($sk,$chip){
  $link=htmlspecialchars($GLOBALS['cfg_base_url'].'/public/su_kien.php?id='.$sk['id']);
  $tieu_de=htmlspecialchars($sk['tieu_de']); $bg=trim((string)($sk['anh_bia']??''));
  if($bg===''){ return '<a class="o-anh" href="'.$link.'" style="background:#ff8c00;display:flex;align-items:flex-end"><div class="chip-nho">'.$chip.'</div><div class="tieu_de" style="color:#fff;padding:12px;font-weight:700">'.$tieu_de.'</div></a>'; }
  return '<a class="o-anh" href="'.$link.'"><div class="bg" style="background-image:url(\''.$bg.'\')"></div><div class="lop"></div><div class="chip-nho">'.$chip.'</div><div class="tieu_de">'.$tieu_de.'</div></a>';
}
$ds1 = ds_theo_tt('sap_toi',3,0);
$ds2 = ds_theo_tt('dang_dien_ra',3,0);
$ds3 = ds_theo_tt('da_ket_thuc',3,0);
include __DIR__ . '/../layout/header.php';
?>
<div id="home-page" class="home-page" data-base-url="<?= htmlspecialchars($cfg_base_url) ?>">
  <div class="banner-do full-screen">
  <img src="<?= htmlspecialchars($logo) ?>" alt="Logo" style="height:400px">
  </div>

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
    <div class="home-see-more"><button class="btn-more" data-nhom="sap_toi">Xem thêm</button></div>
  </section>

  <!-- Pane: Đang diễn ra -->
  <section class="home-pane" data-nhom="dang_dien_ra">
    <h3 class="home-h3">Đang diễn ra</h3>
    <div class="home-grid" data-nhom="dang_dien_ra">
      <?php foreach($ds2 as $sk) echo render_card($sk, chip('dang_dien_ra')); if(empty($ds2)) echo '<p class="nho">Không có sự kiện.</p>';?>
    </div>
    <div class="home-see-more"><button class="btn-more" data-nhom="dang_dien_ra">Xem thêm</button></div>
  </section>

  <!-- Pane: Đã kết thúc -->
  <section class="home-pane" data-nhom="da_ket_thuc">
    <h3 class="home-h3">Đã kết thúc</h3>
    <div class="home-grid" data-nhom="da_ket_thuc">
      <?php foreach($ds3 as $sk) echo render_card($sk, chip('da_ket_thuc')); if(empty($ds3)) echo '<p class="nho">Không có sự kiện.</p>';?>
    </div>
    <div class="home-see-more"><button class="btn-more" data-nhom="da_ket_thuc">Xem thêm</button></div>
  </section>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>
