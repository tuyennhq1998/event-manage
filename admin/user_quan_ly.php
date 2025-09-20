<?php
require_once __DIR__ . '/../functions.php';
bat_buoc_admin();


// Doi vai tro
if (!empty($_POST['hanh_dong']) && $_POST['hanh_dong'] === 'doi_vai_tro') {
$id = (int)$_POST['id'];
$vai_tro = $_POST['vai_tro'] === 'admin' ? 'admin' : 'user';
$stm = $ket_noi->prepare('UPDATE users SET vai_tro = ? WHERE id = ?');
$stm->execute([$vai_tro, $id]);
}


// Xoa user
if (!empty($_GET['hanh_dong']) && $_GET['hanh_dong'] === 'xoa' && !empty($_GET['id'])) {
$id = (int)$_GET['id'];
$stm = $ket_noi->prepare('DELETE FROM users WHERE id = ?');
$stm->execute([$id]);
}


$ds = $ket_noi->query('SELECT * FROM users ORDER BY ngay_tao DESC')->fetchAll(PDO::FETCH_ASSOC);
include __DIR__ . '/../layout/header.php';
?>
<h2>Quan ly user</h2>
<div class="the">
    <table width="100%" border="1" cellspacing="0" cellpadding="6">
        <tr>
            <th>ID</th>
            <th>Ten</th>
            <th>Email</th>
            <th>Vai tro</th>
            <th>Hanh dong</th>
        </tr>
        <?php foreach ($ds as $u): ?>
        <tr>
            <td><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['ten']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td>
                <form method="post" style="display:inline-flex; gap:6px; align-items:center">
                    <input type="hidden" name="hanh_dong" value="doi_vai_tro">
                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                    <select name="vai_tro">
                        <option value="user" <?= $u['vai_tro']==='user'?'selected':'' ?>>user</option>
                        <option value="admin" <?= $u['vai_tro']==='admin'?'selected':'' ?>>admin</option>
                    </select>
                    <button class="nut" type="submit">Luu</button>
                </form>
            </td>
            <td>
                <a class="nut" onclick="return confirm('Xoa user nay?')"
                    href="?hanh_dong=xoa&id=<?= $u['id'] ?>">Xoa</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>