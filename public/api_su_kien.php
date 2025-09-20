<?php
require_once __DIR__ . '/../functions.php';

$tt       = $_GET['tt'] ?? 'sap_toi';                 // sap_toi | dang_dien_ra | da_ket_thuc
$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = max(1, min(24, (int)($_GET['per_page'] ?? 3)));
$offset   = ($page - 1) * $per_page;

$now = date('Y-m-d H:i:s');
if ($tt === 'sap_toi') {
  $where = 'thoi_gian_bat_dau > :now';                     $order = ' ORDER BY thoi_gian_bat_dau ASC ';
} elseif ($tt === 'dang_dien_ra') {
  $where = 'thoi_gian_bat_dau <= :now AND thoi_gian_ket_thuc >= :now';  $order = ' ORDER BY thoi_gian_bat_dau ASC ';
} else {
  $where = 'thoi_gian_ket_thuc < :now';                     $order = ' ORDER BY thoi_gian_bat_dau DESC ';
}

$stmC = $ket_noi->prepare("SELECT COUNT(*) FROM events WHERE $where");
$stmD = $ket_noi->prepare("SELECT * FROM events WHERE $where $order LIMIT :lim OFFSET :off");
$stmC->bindValue(':now',$now); $stmD->bindValue(':now',$now);
$stmD->bindValue(':lim',$per_page,PDO::PARAM_INT); $stmD->bindValue(':off',$offset,PDO::PARAM_INT);
$stmC->execute(); $tong=(int)$stmC->fetchColumn();
$stmD->execute(); $rows=$stmD->fetchAll(PDO::FETCH_ASSOC);

function card_html($sk, $chip){
  $link = htmlspecialchars($GLOBALS['cfg_base_url'].'/public/su_kien.php?id='.$sk['id']);
  $tieu_de = htmlspecialchars($sk['tieu_de']);
  $bg = trim((string)($sk['anh_bia'] ?? ''));
  if ($bg===''){
    return '<a class="o-anh" href="'.$link.'" style="background:#ff8c00;display:flex;align-items:flex-end"><div class="chip-nho">'.$chip.'</div><div class="tieu_de" style="color:#fff;padding:12px;font-weight:700">'.$tieu_de.'</div></a>';
  }
  return '<a class="o-anh" href="'.$link.'"><div class="bg" style="background-image:url(\''.$bg.'\')"></div><div class="lop"></div><div class="chip-nho">'.$chip.'</div><div class="tieu_de">'.$tieu_de.'</div></a>';
}
$chip = $tt==='sap_toi'?'Sắp tới':($tt==='dang_dien_ra'?'Đang diễn ra':'Đã kết thúc');
$html = '';
foreach($rows as $r){ $html .= card_html($r,$chip); }
$loaded = $offset + count($rows);
$has_more = $loaded < $tong;

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['ok'=>true,'html'=>$html,'has_more'=>$has_more,'loaded'=>$loaded,'total'=>$tong]);
