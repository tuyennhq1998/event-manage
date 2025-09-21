<?php
require_once __DIR__ . '/../functions.php';
bat_buoc_dang_nhap();

$uid = (int)($_SESSION['user_id'] ?? 0);

function my_events_by_status(int $uid, string $tt, int $limit=3, int $offset=0): array {
  global $ket_noi;
  $now = date('Y-m-d H:i:s');

  if ($tt === 'sap_toi') {
    $where = 'e.thoi_gian_bat_dau > :now';
    $order = ' ORDER BY e.thoi_gian_bat_dau ASC ';
  } elseif ($tt === 'dang_dien_ra') {
    $where = 'e.thoi_gian_bat_dau <= :now AND e.thoi_gian_ket_thuc >= :now';
    $order = ' ORDER BY e.thoi_gian_bat_dau ASC ';
  } else {
    $where = 'e.thoi_gian_ket_thuc < :now';
    $order = ' ORDER BY e.thoi_gian_bat_dau DESC ';
  }

  $sql = "SELECT e.*
          FROM events e
          INNER JOIN event_registrations r ON r.su_kien_id = e.id AND r.user_id = :uid
          WHERE $where
          GROUP BY e.id
          $order
          LIMIT :lim OFFSET :off";
  $stm = $ket_noi->prepare($sql);
  $stm->bindValue(':uid', $uid, PDO::PARAM_INT);
  $stm->bindValue(':now', $now);
  $stm->bindValue(':lim', $limit, PDO::PARAM_INT);
  $stm->bindValue(':off', $offset, PDO::PARAM_INT);
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

$ds1 = my_events_by_status($uid, 'sap_toi',      3, 0);
$ds2 = my_events_by_status($uid, 'dang_dien_ra', 3, 0);
$ds3 = my_events_by_status($uid, 'da_ket_thuc',  3, 0);

include __DIR__ . '/../layout/header.php';
?>
<div id="my-events-page" data-base-url="<?= htmlspecialchars($cfg_base_url) ?>">
  <h2 style="margin-bottom:12px">Sự kiện của tôi</h2>

  <div class="home-tabs">
    <button class="tab active" data-nhom="sap_toi">Sắp tới</button>
    <button class="tab" data-nhom="dang_dien_ra">Đang diễn ra</button>
    <button class="tab" data-nhom="da_ket_thuc">Đã kết thúc</button>
  </div>

  <section class="home-pane active" data-nhom="sap_toi">
    <h3 class="home-h3">Sắp tới</h3>
    <div class="home-grid" data-nhom="sap_toi">
      <?php foreach($ds1 as $sk) echo render_card($sk, chip('sap_toi')); if(empty($ds1)) echo '<p class="nho">Chưa có sự kiện.</p>'; ?>
    </div>
    <div class="home-see-more"><button class="btn-more-my" data-nhom="sap_toi">Xem thêm</button></div>
  </section>

  <section class="home-pane" data-nhom="dang_dien_ra">
    <h3 class="home-h3">Đang diễn ra</h3>
    <div class="home-grid" data-nhom="dang_dien_ra">
      <?php foreach($ds2 as $sk) echo render_card($sk, chip('dang_dien_ra')); if(empty($ds2)) echo '<p class="nho">Chưa có sự kiện.</p>'; ?>
    </div>
    <div class="home-see-more"><button class="btn-more-my" data-nhom="dang_dien_ra">Xem thêm</button></div>
  </section>

  <section class="home-pane" data-nhom="da_ket_thuc">
    <h3 class="home-h3">Đã kết thúc</h3>
    <div class="home-grid" data-nhom="da_ket_thuc">
      <?php foreach($ds3 as $sk) echo render_card($sk, chip('da_ket_thuc')); if(empty($ds3)) echo '<p class="nho">Chưa có sự kiện.</p>'; ?>
    </div>
    <div class="home-see-more"><button class="btn-more-my" data-nhom="da_ket_thuc">Xem thêm</button></div>
  </section>
</div>

<script>
// ⚠️ KHÔNG dùng handler .btn-more của trang dịch vụ
// Handler RIÊNG cho trang "Sự kiện của tôi"

document.addEventListener('click', function(e){
  // tab
  const t = e.target.closest('.home-tabs .tab');
  if (t){
    const nhom = t.dataset.nhom;
    document.querySelectorAll('.home-tabs .tab').forEach(b=>b.classList.toggle('active', b===t));
    document.querySelectorAll('.home-pane').forEach(p=>p.classList.toggle('active', p.dataset.nhom===nhom));
  }

  // xem thêm (riêng)
  const more = e.target.closest('.btn-more-my');
  if (more){
    e.preventDefault();
    const wrap  = document.getElementById('my-events-page');
    const base  = wrap.dataset.baseUrl || '';
    const nhom  = more.dataset.nhom;
    const grid  = document.querySelector(`.home-grid[data-nhom="${nhom}"]`);
    const offset = grid?.querySelectorAll('.o-anh').length || 0;

    fetch(`${base}/public/ajax_more_my_events.php?nhom=${encodeURIComponent(nhom)}&offset=${offset}&limit=3`,
          {credentials:'same-origin'})
      .then(r=>r.text())
      .then(html=>{
        if (!html.trim()){ more.disabled = true; more.textContent = 'Hết dữ liệu'; return; }
        grid.insertAdjacentHTML('beforeend', html);
      })
      .catch(()=>{ /* optional notify */ });
  }
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
