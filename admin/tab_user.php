<?php
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../model/User.php';
$user = new User($ket_noi);

$user->bat_buoc_admin();

$base = $cfg_base_url;

// --- lọc & phân trang ---
$per_page = max(1, (int)($_GET['per_page'] ?? 10));
$page     = max(1, (int)($_GET['page'] ?? 1));
$q        = trim($_GET['q'] ?? '');
$offset   = ($page - 1) * $per_page;

$where = '1=1'; $params=[];
if ($q !== '') {
  $where .= " AND (ten LIKE :kw OR email LIKE :kw)";
  $params[':kw'] = "%$q%";
}

// đếm
$stm = $ket_noi->prepare("SELECT COUNT(*) FROM users WHERE $where");
foreach ($params as $k=>$v) $stm->bindValue($k,$v);
$stm->execute();
$tong  = (int)$stm->fetchColumn();
$pages = max(1, (int)ceil($tong / $per_page));

// dữ liệu
$sql = "SELECT id, ten, email, ngay_tao FROM users WHERE $where
        ORDER BY ngay_tao DESC LIMIT :limit OFFSET :offset";
$stm = $ket_noi->prepare($sql);
foreach ($params as $k=>$v) $stm->bindValue($k,$v);
$stm->bindValue(':limit',$per_page,PDO::PARAM_INT);
$stm->bindValue(':offset',$offset,PDO::PARAM_INT);
$stm->execute();
$ds = $stm->fetchAll(PDO::FETCH_ASSOC);

function link_page($p,$per_page,$q){
  return '?'.http_build_query(['page'=>$p,'per_page'=>$per_page,'q'=>$q]);
}
?>
<div class="hang" style="justify-content:space-between;align-items:center;margin-bottom:10px">
  <h3 style="margin:0">Quản lý user</h3>
  <a class="nut chinh" href="<?= $base ?>/admin/user_quan_ly.php">➕ Thêm user</a>
</div>

<form class="form-tim" id="tim_user">
  <input type="search" name="q" placeholder="Tìm theo tên hoặc email…" value="<?= htmlspecialchars($q) ?>">
  <button class="nut" type="submit">🔎 Tìm</button>
  <?php if ($q!==''): ?>
    <a class="nut phu" href="<?= link_page(1,$per_page,'') ?>">✖ Xóa lọc</a>
  <?php endif; ?>
</form>

<div class="the" style="padding:0">
  <table>
    <thead>
      <tr>
        <th style="width:70px">ID</th>
        <th style="width:220px">Tên</th>
        <th>Email</th>
        <th>Vai trò</th>
        <th style="width:180px">Ngày tạo</th>
        <th style="width:160px">Hành động</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($ds as $u): ?>
      <tr>
        <td><?= $u['id'] ?></td>
        <td><?= htmlspecialchars($u['ten']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><?= htmlspecialchars($u['vai_tro']) ?>
        </td>
        <td style="font-variant-numeric:tabular-nums"><?= htmlspecialchars($u['ngay_tao']) ?></td>
        <td>
          <a class="nut" href="<?= $base ?>/admin/user_quan_ly.php?hanh_dong=form_sua&id=<?= $u['id'] ?>">✏️</a>
          <a class="nut" onclick="return confirm('Xóa user này? Hành động không thể hoàn tác.')" href="<?= $base ?>/admin/user_quan_ly.php?hanh_dong=xoa&id=<?= $u['id'] ?>">🗑️</a>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($ds)): ?>
      <tr><td colspan="5"><i>Không có dữ liệu</i></td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="phan-trang" data-per-page="<?= (int)$per_page ?>" data-q="<?= htmlspecialchars($q) ?>">
  <span class="nho">Tổng: <?= $tong ?> — Trang <?= $page ?>/<?= $pages ?></span>
  <div class="nut-nhom">
    <a class="nut <?= $page<=1?'vohieu':'' ?>" data-page="1" href="<?= link_page(1,$per_page,$q) ?>">⏮ Đầu</a>
    <a class="nut <?= $page<=1?'vohieu':'' ?>" data-page="<?= $page-1 ?>" href="<?= link_page(max(1,$page-1),$per_page,$q) ?>">◀ Trước</a>
    <?php $from=max(1,$page-2); $to=min($pages,$page+2); for($i=$from;$i<=$to;$i++): ?>
      <a class="nut <?= $i===$page?'chinh':'' ?>" data-page="<?= $i ?>" href="<?= link_page($i,$per_page,$q) ?>"><?= $i ?></a>
    <?php endfor; ?>
    <a class="nut <?= $page>=$pages?'vohieu':'' ?>" data-page="<?= $page+1 ?>" href="<?= link_page(min($pages,$page+1),$per_page,$q) ?>">Sau ▶</a>
    <a class="nut <?= $page>=$pages?'vohieu':'' ?>" data-page="<?= $pages ?>" href="<?= link_page($pages,$per_page,$q) ?>">Cuối ⏭</a>
  </div>
</div>
