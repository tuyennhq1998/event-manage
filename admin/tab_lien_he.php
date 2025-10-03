<?php
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../model/User.php';
require_once __DIR__ . '/../model/Contact.php';
$contact = new Contact($ket_noi);
$user = new User($ket_noi);

$user->bat_buoc_admin();

$base = $cfg_base_url;

// lọc & phân trang
$per_page = max(1, (int)($_GET['per_page'] ?? 10));
$page     = max(1, (int)($_GET['page'] ?? 1));
$q        = trim($_GET['q'] ?? '');
$offset   = ($page - 1) * $per_page;

$tong  = $contact->dem_lien_he($q);
$pages = max(1, (int)ceil($tong / $per_page));
$ds    = $contact->danh_sach_lien_he($page, $per_page, $q);

function link_page($p, $per, $q)
{
  return '?' . http_build_query(['page' => $p, 'per_page' => $per, 'q' => $q]);
}
?>
<h3>Quản lý liên hệ</h3>

<form class="form-tim" id="tim_lien_he">
    <input type="search" name="q" placeholder="Tìm theo họ tên / email / tiêu đề…" value="<?= htmlspecialchars($q) ?>">
    <button class="nut" type="submit">🔎 Tìm</button>
    <?php if ($q !== ''): ?>
    <a class="nut phu" href="<?= link_page(1, $per_page, '') ?>">✖ Xóa lọc</a>
    <?php endif; ?>
</form>

<div class="the" style="padding:0">
    <table>
        <thead>
            <tr>
                <th style="width:70px">ID</th>
                <th style="width:220px">Họ tên</th>
                <th>Email</th>
                <th style="width:220px">Tiêu đề</th>
                <th style="width:160px">Ngày gửi</th>
                <th style="width:130px">Trạng thái</th>
                <th style="width:210px">Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ds as $r): ?>
            <tr>
                <td><?= (int)$r['id'] ?></td>
                <td><?= htmlspecialchars($r['ho_ten']) ?></td>
                <td><?= htmlspecialchars($r['email']) ?></td>
                <td title="<?= htmlspecialchars($r['tieu_de']) ?>">
                    <?= htmlspecialchars(mb_strimwidth($r['tieu_de'], 0, 35, '…', 'UTF-8')) ?></td>
                <td style="font-variant-numeric:tabular-nums"><?= htmlspecialchars($r['ngay_gui']) ?></td>
                <td>
                    <?php if ($r['trang_thai'] === 'moi'): ?>
                    <span class="chip saptoi">Mới</span>
                    <?php else: ?>
                    <span class="chip dangdienra">Đã xử lý</span>
                    <?php endif; ?>
                </td>
                <td>
                    <button class="nut xem-lh" type="button" data-id="<?= (int)$r['id'] ?>">👁️ Xem</button>
                    <?php if ($r['trang_thai'] === 'moi'): ?>
                    <button class="nut phu xl-lh" type="button" data-id="<?= (int)$r['id'] ?>">✅ Đánh dấu xử lý</button>
                    <?php endif; ?>
                    <button class="nut xoa-lh" type="button" data-id="<?= (int)$r['id'] ?>">🗑️ Xóa</button>
                </td>

            </tr>
            <?php endforeach; ?>
            <?php if (empty($ds)): ?>
            <tr>
                <td colspan="7"><i>Không có dữ liệu</i></td>
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

<!-- Overlay riêng cho Liên hệ (KHÔNG dùng popup_nen để tránh va chạm) -->
<div class="lh-overlay" id="lh-overlay" style="display:none">
    <div class="lh-box">
        <div class="hang" style="justify-content:space-between;align-items:center;margin-bottom:8px">
            <h3 style="margin:0">Chi tiết liên hệ</h3>
            <button class="nut phu" type="button" data-dong-lh>✖</button>
        </div>
        <div id="lh-noi-dung" class="nho">Đang tải…</div>
    </div>
</div>

<style>
/* Cô lập style cho overlay Liên hệ */
.lh-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, .45);
    z-index: 9999;
}

.lh-overlay .lh-box {
    background: #fff;
    color: #111;
    border-radius: 12px;
    padding: 14px;
    max-width: 720px;
    width: clamp(320px, 92vw, 720px);
    margin: 8vh auto;
    box-shadow: 0 10px 30px rgba(0, 0, 0, .2);
}
</style>

<script>
document.addEventListener('click', async function(e) {
    const xem = e.target.closest('.xem-lh');
    const xl = e.target.closest('.xl-lh');
    const xoa = e.target.closest('.xoa-lh');
    console.log('aaa');
    // Xem chi tiết
    if (xem) {
        e.preventDefault();
        const id = xem.dataset.id;
        const overlay = document.getElementById('lh-overlay');
        const box = document.getElementById('lh-noi-dung');
        overlay.style.display = 'block';
        box.textContent = 'Đang tải…';
        try {
            const res = await fetch(base + '/admin/lien_he_xem.php?id=' + id, {
                credentials: 'same-origin'
            });
            box.innerHTML = await res.text();
        } catch (err) {
            box.innerHTML = '<p style="color:red">Không tải được chi tiết.</p>';
        }
    }

    // Đóng overlay
    if (e.target.matches('[data-dong-lh]')) {
        document.getElementById('lh-overlay').style.display = 'none';
    }

    // Đánh dấu xử lý
    if (xl) {
        e.preventDefault();
        const id = xl.dataset.id;
        if (!confirm('Đánh dấu đã xử lý liên hệ #' + id + '?')) return;
        const res = await fetch(base + '/admin/lien_he_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'hanh_dong=xu_ly&id=' + encodeURIComponent(id)
        });
        if (res.ok) {
            loadAdminTab('lien-he', <?= (int)$page ?>);
        }
    }

    // Xóa liên hệ
    if (xoa) {
        e.preventDefault();
        const id = xoa.dataset.id;
        if (!confirm('Xóa liên hệ #' + id + '?')) return;
        const res = await fetch(base + '/admin/lien_he_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'hanh_dong=xoa&id=' + encodeURIComponent(id)
        });
        if (res.ok) {
            loadAdminTab('lien-he', <?= (int)$page ?>);
        }
    }
});

// Submit search form (AJAX)
document.addEventListener('submit', function(e) {
    const f = e.target.closest('#tim_lien_he');
    if (!f) return;
    e.preventDefault();
    loadAdminTab('lien-he', 1);
});

// Input search (debounce)
let timer = null;
document.addEventListener('input', function(e) {
    if (!e.target.closest('#tim_lien_he')) return;
    clearTimeout(timer);
    timer = setTimeout(() => loadAdminTab('lien-he', 1), 400);
});
</script>