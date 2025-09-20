<?php require_once __DIR__ . '/../config.php';require_once __DIR__ . '/../functions.php'; 
$logo   = get_option('site_logo');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quản lý sự kiện</title>
<link rel="stylesheet" href="<?= $cfg_base_url ?>/assets/style.css">

</head>
<body>
<header class="khung">
<div class="hang">
<div class="cot trai"><a href="<?= $cfg_base_url ?>/public/index.php" class="logo"><img src="<?= htmlspecialchars($logo) ?>" alt="Logo" style="height:70px"></a></div>
<nav class="cot phai">
<?php if (!empty($_SESSION['user_id'])): ?>
<span>Xin chao, <b><?= htmlspecialchars($_SESSION['user_ten']) ?></b></span>
<?php if (($_SESSION['user_vai_tro'] ?? 'user') === 'admin'): ?>
| <a href="<?= $cfg_base_url ?>/admin/index.php">Admin</a>
<?php endif; ?>
| <a href="<?= $cfg_base_url ?>/public/dang_xuat.php">Đăng xuất</a>
<?php else: ?>
<a href="<?= $cfg_base_url ?>/public/dang_nhap.php">Đăng nhập</a>
| <a href="<?= $cfg_base_url ?>/public/dang_ky_tai_khoan.php">Đăng ký</a>
<?php endif; ?>
</nav>
</div>
</header>
<main class="khung">