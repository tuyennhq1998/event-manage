<?php
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../model/User.php';
$user = new User($ket_noi);

$user->bat_buoc_admin();



$sql = 'SELECT e.id, e.tieu_de, e.thoi_gian_bat_dau, e.thoi_gian_ket_thuc, COUNT(r.id) AS so_nguoi
FROM events e
LEFT JOIN event_registrations r ON r.su_kien_id = e.id
GROUP BY e.id
ORDER BY e.thoi_gian_bat_dau DESC';
$rows = $ket_noi->query($sql)->fetchAll(PDO::FETCH_ASSOC);


include __DIR__ . '/../layout/header.php';
?>
<h2>Bao cao tham gia su kien</h2>
<div class="the">
    <table width="100%" border="1" cellspacing="0" cellpadding="6">
        <tr>
            <th>ID</th>
            <th>Tieu de</th>
            <th>Thoi gian</th>
            <th>So nguoi tham gia</th>
        </tr>
        <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= $r['id'] ?></td>
            <td><?= htmlspecialchars($r['tieu_de']) ?></td>
            <td><?= htmlspecialchars($r['thoi_gian_bat_dau']) ?> → <?= htmlspecialchars($r['thoi_gian_ket_thuc']) ?>
            </td>
            <td><b><?= (int)$r['so_nguoi'] ?></b></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>