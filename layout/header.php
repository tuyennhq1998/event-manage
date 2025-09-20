<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
$logo = get_option('site_logo');
$is_login = !empty($_SESSION['user_id']);
$is_admin = ($is_login && (($_SESSION['user_vai_tro'] ?? 2) == 'admin')); // 1 = admin
$user_name = $is_login ? ($_SESSION['user_ten'] ?? 'Bạn') : '';
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

<header class="site-header khung">
  <div class="hang">
    <!-- Logo -->
    <div class="cot trai">
      <a href="<?= $cfg_base_url ?>/public/index.php" class="logo"
         aria-label="Trang chủ">
        <img src="<?= htmlspecialchars($logo) ?>" alt="Logo" style="height:70px">
      </a>
    </div>

    <!-- Main nav -->
    <nav class="cot phai header-nav">
      <ul class="main-menu">
        <li><a href="<?= $cfg_base_url ?>/public/index.php">Trang chủ</a></li>
        <li><a href="<?= $cfg_base_url ?>/public/dich_vu.php">Dịch vụ</a></li>
        <li><a href="#">Giới thiệu</a></li>
        <li><a href="#">Liên hệ</a></li>
      </ul>

      <!-- User / Auth dropdown -->
      <?php if (!$is_login): ?>
        <!-- Chưa đăng nhập: icon người dùng + dropdown Đăng nhập/Đăng ký -->
        <div class="dropdown user-dropdown" data-dropdown>
          <button class="dropdown-toggle icon-only" aria-haspopup="true" aria-expanded="false" data-dropdown-toggle>
            <!-- icon user -->
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                 xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5Z" fill="currentColor"/>
            </svg>
          </button>
          <ul class="dropdown-menu">
            <li><a href="<?= $cfg_base_url ?>/public/dang_nhap.php">Đăng nhập</a></li>
            <li><a href="<?= $cfg_base_url ?>/public/dang_ky_tai_khoan.php">Đăng ký</a></li>
          </ul>
        </div>
      <?php else: ?>
        <!-- Đã đăng nhập: Xin chào Tên + dropdown -->
        <div class="dropdown user-dropdown" data-dropdown>
          <button class="dropdown-toggle" aria-haspopup="true" aria-expanded="false" data-dropdown-toggle>
            Xin chào, <b><?= htmlspecialchars($user_name) ?></b>
          </button>
          <ul class="dropdown-menu">
            <li><a href="<?= $cfg_base_url ?>/public/su_kien_cua_toi.php">Sự kiện của tôi</a></li>
            <?php if ($is_admin): ?>
              <li><a href="<?= $cfg_base_url ?>/admin/index.php">Admin</a></li>
            <?php endif; ?>
            <li><a href="<?= $cfg_base_url ?>/public/dang_xuat.php">Đăng xuất</a></li>
          </ul>
        </div>
      <?php endif; ?>
    </nav>
  </div>
</header>
<script>
    // Dropdown click handler
document.addEventListener('DOMContentLoaded', function() {
  // Lấy tất cả dropdown
  const dropdowns = document.querySelectorAll('[data-dropdown]');
  
  dropdowns.forEach(dropdown => {
    const toggle = dropdown.querySelector('[data-dropdown-toggle]');
    const menu = dropdown.querySelector('.dropdown-menu');
    
    if (toggle && menu) {
      // Click vào button để toggle dropdown
      toggle.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Đóng tất cả dropdown khác
        dropdowns.forEach(otherDropdown => {
          if (otherDropdown !== dropdown) {
            otherDropdown.removeAttribute('data-open');
          }
        });
        
        // Toggle dropdown hiện tại
        if (dropdown.hasAttribute('data-open')) {
          dropdown.removeAttribute('data-open');
        } else {
          dropdown.setAttribute('data-open', '1');
        }
      });
    }
  });
  
  // Click ra ngoài để đóng dropdown
  document.addEventListener('click', function(e) {
    dropdowns.forEach(dropdown => {
      if (!dropdown.contains(e.target)) {
        dropdown.removeAttribute('data-open');
      }
    });
  });
  
  // ESC để đóng dropdown
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      dropdowns.forEach(dropdown => {
        dropdown.removeAttribute('data-open');
      });
    }
  });
  
  // Ngăn menu dropdown đóng khi click vào các link bên trong
  document.querySelectorAll('.dropdown-menu a').forEach(link => {
    link.addEventListener('click', function(e) {
      // Để link hoạt động bình thường, chỉ đóng dropdown sau khi navigate
      setTimeout(() => {
        dropdowns.forEach(dropdown => {
          dropdown.removeAttribute('data-open');
        });
      }, 100);
    });
  });
});
</script>
<main class="khung">
