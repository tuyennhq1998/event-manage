<?php
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../model/DichVu.php';
require_once __DIR__ . '/../model/User.php';

$dv = new SuKien($ket_noi);
$user = new User($ket_noi);

$user->bat_buoc_admin();

$base = $cfg_base_url;

// --- lọc & phân trang ---
$per_page = max(1, (int)($_GET['per_page'] ?? 10));
$page     = max(1, (int)($_GET['page'] ?? 1));
$q        = trim($_GET['q'] ?? '');
$offset   = ($page - 1) * $per_page;

// where + params
$where = '1=1';
$params = [];
if ($q !== '') {
  $where .= " AND (tieu_de LIKE :kw OR dia_diem LIKE :kw OR mo_ta LIKE :kw)";
  $params[':kw'] = "%$q%";
}

// đếm
$sqlCount = "SELECT COUNT(*) FROM events WHERE $where";
$stm = $ket_noi->prepare($sqlCount);
foreach ($params as $k => $v) $stm->bindValue($k, $v);
$stm->execute();
$tong = (int)$stm->fetchColumn();
$pages = max(1, (int)ceil($tong / $per_page));

// dữ liệu
$sql = "SELECT * FROM events WHERE $where
        ORDER BY thoi_gian_bat_dau DESC
        LIMIT :limit OFFSET :offset";
$stm = $ket_noi->prepare($sql);
foreach ($params as $k => $v) $stm->bindValue($k, $v);
$stm->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stm->bindValue(':offset', $offset, PDO::PARAM_INT);
$stm->execute();
$ds = $stm->fetchAll(PDO::FETCH_ASSOC);

function link_page($p, $per_page, $q)
{
  $qs = http_build_query(['page' => $p, 'per_page' => $per_page, 'q' => $q]);
  return '?' . $qs;
}
?>
<div class="hang" style="justify-content:space-between;align-items:center;margin-bottom:10px">
    <h3 style="margin:0">Quản lý sự kiện</h3>
    <a class="nut chinh" href="<?= $base ?>/admin/sukien_quan_ly.php">➕ Thêm sự kiện</a>
</div>

<form class="form-tim" id="tim_su_kien">
    <input type="search" name="q" placeholder="Tìm theo tiêu đề, địa điểm…" value="<?= htmlspecialchars($q) ?>">
    <button class="nut" type="submit">🔎 Tìm</button>
</form>

<div class="the" style="padding:0">
    <table>
        <thead>
            <tr>
                <th style="width:60px">ID</th>
                <th>Tiêu đề</th>
                <th style="width:280px">Thời gian</th>
                <th style="width:220px">Địa điểm</th>
                <th style="width:240px">Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ds as $sk): ?>
            <tr>
                <td><?= $sk['id'] ?></td>
                <td><?= htmlspecialchars($sk['tieu_de']) ?></td>
                <td><?= htmlspecialchars($sk['thoi_gian_bat_dau']) ?> →
                    <?= htmlspecialchars($sk['thoi_gian_ket_thuc']) ?></td>
                <td><?= htmlspecialchars($sk['dia_diem']) ?></td>
                <td>
                    <a class="nut"
                        href="<?= $base ?>/admin/sukien_quan_ly.php?hanh_dong=form_sua&id=<?= $sk['id'] ?>">✏️</a>
                    <a class="nut" onclick="return confirm('Xóa sự kiện này?')"
                        href="<?= $base ?>/admin/sukien_quan_ly.php?hanh_dong=xoa&id=<?= $sk['id'] ?>">🗑️</a>
                    <a class="nut" target="_blank" href="<?= $base ?>/public/su_kien.php?id=<?= $sk['id'] ?>">🔎</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($ds)): ?>
            <tr>
                <td colspan="5"><i>Không có dữ liệu</i></td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="phan-trang" data-per-page="<?= (int)$per_page ?>" data-q="<?= htmlspecialchars($q) ?>">
    <span class="nho">Tổng: <?= $tong ?> — Trang <?= $page ?>/<?= $pages ?></span>
    <div class="nut-nhom">
        <a class="nut <?= $page <= 1 ? 'vohieu' : '' ?>" data-page="1" href="<?= link_page(1, $per_page, $q) ?>">⏮
            Đầu</a>
        <a class="nut <?= $page <= 1 ? 'vohieu' : '' ?>" data-page="<?= $page - 1 ?>"
            href="<?= link_page(max(1, $page - 1), $per_page, $q) ?>">◀ Trước</a>
        <?php $from = max(1, $page - 2);
    $to = min($pages, $page + 2);
    for ($i = $from; $i <= $to; $i++): ?>
        <a class="nut <?= $i === $page ? 'chinh' : '' ?>" data-page="<?= $i ?>"
            href="<?= link_page($i, $per_page, $q) ?>"><?= $i ?></a>
        <?php endfor; ?>
        <a class="nut <?= $page >= $pages ? 'vohieu' : '' ?>" data-page="<?= $page + 1 ?>"
            href="<?= link_page(min($pages, $page + 1), $per_page, $q) ?>">Sau ▶</a>
        <a class="nut <?= $page >= $pages ? 'vohieu' : '' ?>" data-page="<?= $pages ?>"
            href="<?= link_page($pages, $per_page, $q) ?>">Cuối ⏭</a>
    </div>
</div>