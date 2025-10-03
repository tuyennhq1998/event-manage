<?php
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../model/User.php';
$user = new User($ket_noi);

$user->bat_buoc_admin();

header('Content-Type: text/html; charset=utf-8');

/* schema */
$TABLE_JOIN   = 'event_registrations';
$EVENT_ID_COL = 'su_kien_id'; // nếu DB là event_id: 'event_id'

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  http_response_code(400);
  echo '<div class="the"><p style="color:#dc2626">Thiếu ID sự kiện.</p></div>';
  exit;
}

/* lọc & phân trang trong popup */
$per_page = max(1, (int)($_GET['per_page'] ?? 10));
$page     = max(1, (int)($_GET['page'] ?? 1));
$q        = trim($_GET['q'] ?? '');
$offset   = ($page - 1) * $per_page;

/* Sự kiện */
$stm = $ket_noi->prepare("SELECT id, tieu_de FROM events WHERE id = :id");
$stm->execute([':id'=>$id]);
$sk = $stm->fetch(PDO::FETCH_ASSOC);
if (!$sk) {
  echo '<div class="the"><p style="color:#dc2626">Sự kiện không tồn tại.</p></div>';
  exit;
}

/* WHERE: tìm theo TÊN — chính xác HOẶC gần đúng */
$where  = "t.{$EVENT_ID_COL} = :id";
$params = [':id'=>$id];
if ($q !== '') {
  $where .= " AND (u.ten = :kw OR u.ten LIKE :kwLike)";
  $params[':kw']     = $q;
  $params[':kwLike'] = "%$q%";
}

/* Đếm tổng */
$sqlCount = "
  SELECT COUNT(*)
  FROM {$TABLE_JOIN} t
  JOIN users u ON u.id = t.user_id
  WHERE $where
";
$stm = $ket_noi->prepare($sqlCount);
foreach ($params as $k=>$v) $stm->bindValue($k,$v);
$stm->execute();
$tong  = (int)$stm->fetchColumn();
$pages = max(1, (int)ceil($tong / $per_page));

/* Danh sách user trang hiện tại */
$sql = "
  SELECT u.id, COALESCE(NULLIF(TRIM(u.ten), ''), u.email) AS ten_hien_thi,
         u.email, t.ngay_dang_ky
  FROM {$TABLE_JOIN} t
  JOIN users u ON u.id = t.user_id
  WHERE $where
  ORDER BY t.ngay_dang_ky DESC, u.id DESC
  LIMIT :lim OFFSET :off
";
$stm = $ket_noi->prepare($sql);
foreach ($params as $k=>$v) $stm->bindValue($k,$v);
$stm->bindValue(':lim', $per_page, PDO::PARAM_INT);
$stm->bindValue(':off', $offset, PDO::PARAM_INT);
$stm->execute();
$rows = $stm->fetchAll(PDO::FETCH_ASSOC);

function link_popup($id, $p, $per_page, $q){
  return '?' . http_build_query(['id'=>$id, 'page'=>$p, 'per_page'=>$per_page, 'q'=>$q]);
}
?>
<style>
  .popup-danh-sach th { color: blue; }
</style>

<div class="the modal-root" data-event-id="<?= (int)$id ?>" style="max-width:780px;padding:0; background-color: white; color: black;">
  <div style="padding:12px 16px;border-bottom:1px solid rgba(0,0,0,.08);display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap">
    <h4 style="margin:0">Người đăng ký – <?= htmlspecialchars($sk['tieu_de']) ?> (<?= $tong ?>)</h4>
    <button class="nut phu dong-modal">✖ Đóng</button>
  </div>

  <form class="modal-form-tim" style="display:flex;gap:8px;align-items:center;padding:12px 16px">
    <input type="search" name="q" placeholder="Nhập tên (đúng hoặc chứa)" value="<?= htmlspecialchars($q) ?>" style="flex:1">
    <button class="nut" type="submit">🔎 Tìm</button>
  </form>

  <div style="max-height:60vh; overflow:auto;" class="popup-danh-sach">
    <table>
      <thead>
        <tr>
          <th style="width:90px">User ID</th>
          <th>Tên hiển thị</th>
          <th style="width:260px">Email</th>
          <th style="width:200px">Thời điểm đăng ký</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= (int)$r['id'] ?></td>
          <td><?= htmlspecialchars($r['ten_hien_thi']) ?></td>
          <td><?= htmlspecialchars($r['email']) ?></td>
          <td style="font-variant-numeric:tabular-nums"><?= htmlspecialchars($r['ngay_dang_ky']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($rows)): ?>
        <tr><td colspan="4"><i>Không có bản ghi phù hợp</i></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="modal-phan-trang" data-per-page="<?= (int)$per_page ?>" data-q="<?= htmlspecialchars($q) ?>" style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;border-top:1px solid rgba(0,0,0,.08)">
    <span class="nho">Tổng: <?= $tong ?> — Trang <?= $page ?>/<?= $pages ?></span>
    <div class="nut-nhom">
      <a class="nut <?= $page<=1?'vohieu':'' ?>" data-page="1" href="<?= link_popup($id,1,$per_page,$q) ?>">⏮ Đầu</a>
      <a class="nut <?= $page<=1?'vohieu':'' ?>" data-page="<?= max(1,$page-1) ?>" href="<?= link_popup($id,max(1,$page-1),$per_page,$q) ?>">◀ Trước</a>
      <?php $from=max(1,$page-2); $to=min($pages,$page+2); for($i=$from;$i<=$to;$i++): ?>
        <a class="nut <?= $i===$page?'chinh':'' ?>" data-page="<?= $i ?>" href="<?= link_popup($id,$i,$per_page,$q) ?>"><?= $i ?></a>
      <?php endfor; ?>
      <a class="nut <?= $page>=$pages?'vohieu':'' ?>" data-page="<?= min($pages,$page+1) ?>" href="<?= link_popup($id,min($pages,$page+1),$per_page,$q) ?>">Sau ▶</a>
      <a class="nut <?= $page>=$pages?'vohieu':'' ?>" data-page="<?= $pages ?>" href="<?= link_popup($id,$pages,$per_page,$q) ?>">Cuối ⏭</a>
    </div>
  </div>
</div>
