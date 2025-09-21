<?php
require_once __DIR__ . '/../functions.php';
bat_buoc_dang_nhap();
header('Content-Type: text/html; charset=UTF-8');

$uid    = (int)($_SESSION['user_id'] ?? 0);
$nhom   = $_GET['nhom']   ?? 'sap_toi';
$offset = max(0, (int)($_GET['offset'] ?? 0));
$limit  = min(24, max(1, (int)($_GET['limit'] ?? 3)));
$q      = trim($_GET['q'] ?? '');
$tu     = trim($_GET['tu_ngay'] ?? '');
$den    = trim($_GET['den_ngay'] ?? '');
$tu_dt  = $tu  ? ($tu  . ' 00:00:00') : null;
$den_dt = $den ? ($den . ' 23:59:59') : null;

function chip($tt){ return $tt==='sap_toi'?'Sắp tới':($tt==='dang_dien_ra'?'Đang diễn ra':'Đã kết thúc'); }
function render_card($sk,$chip_text){
  $link = htmlspecialchars($GLOBALS['cfg_base_url'].'/public/su_kien.php?id='.$sk['id']);
  $tieu_de = htmlspecialchars($sk['tieu_de']);
  $bg = trim((string)($sk['anh_bia'] ?? ''));
  if ($bg === '') {
    return '<a class="o-anh" href="'.$link.'" style="background:#ff8c00;display:flex;align-items:flex-end">'
         .   '<div class="chip-nho">'.$chip_text.'</div>'
         .   '<div class="tieu_de" style="color:#fff;padding:12px;font-weight:700">'.$tieu_de.'</div>'
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

function my_events_more($uid, $tt, $limit, $offset, $q='', $tu_dt=null, $den_dt=null){
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

  $params = [':now'=>$now, ':uid'=>(int)$uid];
  if ($q !== '') { $where .= ' AND e.tieu_de LIKE :kw'; $params[':kw'] = '%'.$q.'%'; }
  if ($tu_dt)     { $where .= ' AND e.thoi_gian_bat_dau >= :tu';  $params[':tu']  = $tu_dt; }
  if ($den_dt)    { $where .= ' AND e.thoi_gian_bat_dau <= :den'; $params[':den'] = $den_dt; }

  $sql = "SELECT e.*
          FROM events e
          JOIN event_registrations r ON r.su_kien_id = e.id
          WHERE r.user_id = :uid AND $where
          $order
          LIMIT :lim OFFSET :off";
  $stm = $ket_noi->prepare($sql);
  foreach($params as $k=>$v) $stm->bindValue($k,$v);
  $stm->bindValue(':lim', (int)$limit,  PDO::PARAM_INT);
  $stm->bindValue(':off', (int)$offset, PDO::PARAM_INT);
  $stm->execute();
  return $stm->fetchAll(PDO::FETCH_ASSOC);
}

$rows = my_events_more($uid, $nhom, $limit, $offset, $q, $tu_dt, $den_dt);
$chip = chip($nhom);

foreach ($rows as $sk) {
  echo render_card($sk, $chip);
}
