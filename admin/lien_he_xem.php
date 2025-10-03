<?php
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../model/User.php';
require_once __DIR__ . '/../model/Contact.php';
$contact = new Contact($ket_noi);

$user = new User($ket_noi);

$user->bat_buoc_admin();

$id = (int)($_GET['id'] ?? 0);
$lh = $id ? $contact->lien_he_theo_id($id) : null;

if (!$lh) {
  http_response_code(404);
  echo '<div class="the"><p style="color:#dc2626">Không tìm thấy liên hệ.</p></div>';
  exit;
}
?>
<div class="the" style="padding:12px">
    <div class="nho">Mã: <b>#<?= (int)$lh['id'] ?></b> — Gửi lúc: <b><?= htmlspecialchars($lh['ngay_gui']) ?></b></div>
    <div class="nho">Họ tên: <b><?= htmlspecialchars($lh['ho_ten']) ?></b></div>
    <div class="nho">Email: <b><?= htmlspecialchars($lh['email']) ?></b>
        <?= $lh['so_dien_thoai'] ? ' — SDT: <b>' . htmlspecialchars($lh['so_dien_thoai']) . '</b>' : '' ?></div>
    <h4 style="margin:10px 0 6px">Tiêu đề: <?= htmlspecialchars($lh['tieu_de']) ?></h4>
    <div style="white-space:pre-wrap; line-height:1.6"><?= nl2br(htmlspecialchars($lh['noi_dung'])) ?></div>
</div>