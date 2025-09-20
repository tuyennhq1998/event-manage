<?php
require_once __DIR__ . '/../functions.php';
bat_buoc_admin();

$base = $cfg_base_url;

/* ====== schema ====== */
$TABLE_JOIN   = 'event_registrations';
$EVENT_ID_COL = 'su_kien_id'; // nếu DB dùng event_id thì đổi 'event_id'

/* --- lọc & phân trang --- */
$per_page = max(1, (int)($_GET['per_page'] ?? 10));
$page     = max(1, (int)($_GET['page'] ?? 1));
$q        = trim($_GET['q'] ?? '');
$offset   = ($page - 1) * $per_page;

/* where + params (GẦN ĐÚNG theo tiêu đề hoặc địa điểm) */
$where  = '1=1';
$params = [];
if ($q !== '') {
  $where .= " AND (e.tieu_de LIKE :kw OR e.dia_diem LIKE :kw)";
  $params[':kw'] = "%$q%";
}

/* đếm tổng */
$sqlCount = "SELECT COUNT(*) FROM events e WHERE $where";
$stm = $ket_noi->prepare($sqlCount);
foreach ($params as $k=>$v) $stm->bindValue($k,$v);
$stm->execute();
$tong  = (int)$stm->fetchColumn();
$pages = max(1, (int)ceil($tong / $per_page));

/* dữ liệu (sắp xếp theo số người tham gia ↓ rồi thời gian ↓) */
$sql = "
  SELECT 
    e.id, e.tieu_de, e.dia_diem, e.thoi_gian_bat_dau, e.thoi_gian_ket_thuc,
    COALESCE(cnt.so_nguoi, 0) AS so_nguoi
  FROM events e
  LEFT JOIN (
    SELECT t.{$EVENT_ID_COL} AS event_id, COUNT(*) AS so_nguoi
    FROM {$TABLE_JOIN} t
    GROUP BY t.{$EVENT_ID_COL}
  ) cnt ON cnt.event_id = e.id
  WHERE $where
  ORDER BY so_nguoi DESC, e.thoi_gian_bat_dau DESC, e.id DESC
  LIMIT :limit OFFSET :offset
";
$stm = $ket_noi->prepare($sql);
foreach ($params as $k=>$v) $stm->bindValue($k,$v);
$stm->bindValue(':limit',$per_page,PDO::PARAM_INT);
$stm->bindValue(':offset',$offset,PDO::PARAM_INT);
$stm->execute();
$rows = $stm->fetchAll(PDO::FETCH_ASSOC);

function link_page($p, $per_page, $q){
  return '?' . http_build_query(['page'=>$p,'per_page'=>$per_page,'q'=>$q]);
}
?>
<h3 style="margin:0 0 10px 0">Báo cáo tham gia</h3>

<form class="form-tim" id="tim_bao_cao">
  <input type="search" name="q" placeholder="Tìm gần đúng theo Tiêu đề hoặc Địa điểm…" value="<?= htmlspecialchars($q) ?>">
  <button class="nut" type="submit">🔎 Tìm</button>
</form>

<div class="the" style="padding:0">
  <table>
    <thead>
      <tr>
        <th style="width:70px">ID</th>
        <th>Tiêu đề</th>
        <th style="width:360px">Thời gian</th>
        <th style="width:160px;text-align:right">Người tham gia</th>
        <th style="width:90px">Xem</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= (int)$r['id'] ?></td>
        <td>
          <div style="font-weight:600"><?= htmlspecialchars($r['tieu_de']) ?></div>
          <div class="nho" style="opacity:.8"><?= htmlspecialchars($r['dia_diem']) ?></div>
        </td>
        <td style="font-variant-numeric:tabular-nums">
          <?= htmlspecialchars($r['thoi_gian_bat_dau']) ?> → <?= htmlspecialchars($r['thoi_gian_ket_thuc']) ?>
        </td>
        <td style="text-align:right"><b><?= (int)$r['so_nguoi'] ?></b></td>
        <td><button class="nut xem-ds" data-event-id="<?= (int)$r['id'] ?>">👀 Xem</button></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($rows)): ?>
      <tr><td colspan="5"><i>Không có dữ liệu</i></td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="phan-trang" data-per-page="<?= (int)$per_page ?>" data-q="<?= htmlspecialchars($q) ?>">
  <span class="nho">Tổng: <?= $tong ?> — Trang <?= $page ?>/<?= $pages ?></span>
  <div class="nut-nhom">
    <a class="nut <?= $page<=1?'vohieu':'' ?>" data-page="1" href="<?= link_page(1,$per_page,$q) ?>">⏮ Đầu</a>
    <a class="nut <?= $page<=1?'vohieu':'' ?>" data-page="<?= max(1,$page-1) ?>" href="<?= link_page(max(1,$page-1),$per_page,$q) ?>">◀ Trước</a>
    <?php $from=max(1,$page-2); $to=min($pages,$page+2); for($i=$from;$i<=$to;$i++): ?>
      <a class="nut <?= $i===$page?'chinh':'' ?>" data-page="<?= $i ?>" href="<?= link_page($i,$per_page,$q) ?>"><?= $i ?></a>
    <?php endfor; ?>
    <a class="nut <?= $page>=$pages?'vohieu':'' ?>" data-page="<?= min($pages,$page+1) ?>" href="<?= link_page(min($pages,$page+1),$per_page,$q) ?>">Sau ▶</a>
    <a class="nut <?= $page>=$pages?'vohieu':'' ?>" data-page="<?= $pages ?>" href="<?= link_page($pages,$per_page,$q) ?>">Cuối ⏭</a>
  </div>
</div>
