</main>
<footer class="khung">
    <p>&copy; <?php echo date('Y'); ?> Event quản lý – Thành công tổ chức</p>
</footer>
<script src="<?= $cfg_base_url ?>/assets/app.js"></script>
<style>
    .menu-button {
        display: none;
        position: fixed;
        bottom: 20px;
        left: 20px;
        width: 60px;
        height: 60px;
        background: white;
        border-radius: 50%;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        cursor: pointer;
        z-index: 1000;
        transition: transform 0.3s ease;
    }

    .menu-button:hover {
        transform: scale(1.1);
    }

    .menu-button:active {
        transform: scale(0.95);
    }

    /* Icon 3 gạch ngang */
    .menu-icon {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 30px;
        height: 20px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .menu-icon span {
        display: block;
        width: 100%;
        height: 3px;
        background: #667eea;
        border-radius: 2px;
        transition: all 0.3s ease;
    }

    /* Animation khi menu mở */
    .menu-button.active .menu-icon span:nth-child(1) {
        transform: translateY(8.5px) rotate(45deg);
    }

    .menu-button.active .menu-icon span:nth-child(2) {
        opacity: 0;
    }

    .menu-button.active .menu-icon span:nth-child(3) {
        transform: translateY(-8.5px) rotate(-45deg);
    }

    /* Menu popup */
    .menu-popup {
        position: fixed;
        bottom: 90px;
        left: 20px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        padding: 15px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(20px);
        transition: all 0.3s ease;
        z-index: 999;
        min-width: 200px;
    }

    .menu-popup.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .menu-item {
        padding: 15px 20px;
        cursor: pointer;
        border-radius: 10px;
        transition: background 0.2s ease;
        color: #333;
        font-size: 16px;
        font-weight: 500;
    }

    .menu-item:hover {
        background: #f0f0f0;
    }

    .menu-item:active {
        background: #e0e0e0;
    }

    /* Chỉ hiện menu button khi màn hình < 640px */
    @media (max-width: 640px) {
        .menu-button {
            display: block;
        }
    }

    /* Demo content */
    .demo-content {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        color: white;
        text-align: center;
        padding: 20px;
    }

    .demo-content h1 {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }

    .demo-content p {
        font-size: 1.2rem;
        opacity: 0.9;
    }

    @media (max-width: 640px) {
        .demo-content h1 {
            font-size: 1.8rem;
        }

        .demo-content p {
            font-size: 1rem;
        }
    }
</style>


<div class="menu-button" id="menuButton">
    <div class="menu-icon">
        <span></span>
        <span></span>
        <span></span>
    </div>
</div>

<!-- Menu Popup -->
<div class="menu-popup" id="menuPopup">
    <div class="menu-item"> <a href="/public/index.php" class="menu-item">🏠 Trang chủ</a></div>
    <div class="menu-item"><a href="/public/dich_vu.php" class="menu-item">💼 Dịch vụ</a></div>
    <div class="menu-item"><a href="/public/gioi_thieu.php" class="menu-item">ℹ️ Giới thiệu</a></div>
    <div class="menu-item"><a href="/public/lien-he.php" class="menu-item">📧 Liên hệ</a></div>

</div>
<script>
    const menuButton = document.getElementById('menuButton');
    const menuPopup = document.getElementById('menuPopup');
    let isMenuOpen = false;

    // Toggle menu khi click vào button
    menuButton.addEventListener('click', function(e) {
        e.stopPropagation();
        isMenuOpen = !isMenuOpen;

        if (isMenuOpen) {
            menuButton.classList.add('active');
            menuPopup.classList.add('show');
        } else {
            menuButton.classList.remove('active');
            menuPopup.classList.remove('show');
        }
    });

    // Đóng menu khi click ra ngoài
    document.addEventListener('click', function(e) {
        if (isMenuOpen && !menuPopup.contains(e.target)) {
            isMenuOpen = false;
            menuButton.classList.remove('active');
            menuPopup.classList.remove('show');
        }
    });

    // Xử lý navigation
    function navigateTo(page) {
        console.log('Điều hướng đến:', page);
        alert('Điều hướng đến: ' + page);

        // Đóng menu sau khi chọn
        isMenuOpen = false;
        menuButton.classList.remove('active');
        menuPopup.classList.remove('show');

        // Thêm code điều hướng của bạn ở đây
        // window.location.href = '/' + page;
    }
</script>
</body>

</html>