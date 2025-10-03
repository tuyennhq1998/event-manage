<?php
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../model/DichVu.php';
$dv = new SuKien($ket_noi);

include __DIR__ . '/../layout/header.php';

$id = (int) ($_GET['id'] ?? 0);
$su_kien = $dv->lay_su_kien_theo_id($id);
if (!$su_kien) {
    echo '<p>❌ Khong tim thay su kien.</p>';
    include __DIR__ . '/../layout/footer.php';
    exit;
}

/* ====== TÍNH TRẠNG THÁI ====== */
$tt = $dv->tinh_trang_thai_su_kien($su_kien['thoi_gian_bat_dau'], $su_kien['thoi_gian_ket_thuc']);
$ten_tt = [
    'sap_toi' => 'Sắp tới',
    'dang_dien_ra' => 'Đang diễn ra',
    'da_ket_thuc' => 'Đã kết thúc'
][$tt] ?? $tt;

/* ====== LẤY GIÁ & GIỚI HẠN, ĐẾM SỐ NGƯỜI ĐÃ ĐĂNG KÝ ====== */
$gia = (int) ($su_kien['gia'] ?? 0);              // cột int/decimal trong DB
$gioi_han = (int) ($su_kien['so_luong'] ?? 0);     // 0 hoặc NULL xem như không giới hạn

// Đếm số người đã đăng ký
$stm = $ket_noi->prepare("SELECT COUNT(*) FROM event_registrations WHERE su_kien_id = :id");
$stm->execute([':id' => $id]);
$so_da_dk = (int) $stm->fetchColumn();

// Tính còn lại nếu có giới hạn
$con_lai = ($gioi_han > 0) ? max(0, $gioi_han - $so_da_dk) : null;

// Format tiền VND
function format_vnd($n)
{
    return $n > 0 ? number_format($n, 0, ',', '.') . ' đ' : 'Miễn phí';
}
$hien_gia = format_vnd($gia);

/* ====== KIỂM TRA USER ĐÃ ĐĂNG KÝ CHƯA ====== */
$da_dang_ky = false;
if (isset($_SESSION['user_id'])) {
    // Nếu có hệ thống đăng nhập với user_id
    $stm_check = $ket_noi->prepare("SELECT COUNT(*) FROM event_registrations WHERE su_kien_id = :su_kien_id AND user_id = :user_id");
    $stm_check->execute([
        ':su_kien_id' => $id,
        ':user_id' => $_SESSION['user_id']
    ]);
    $da_dang_ky = (int) $stm_check->fetchColumn() > 0;
} elseif (isset($_SESSION['user_email'])) {
    // Nếu chỉ có email trong session
    $stm_check = $ket_noi->prepare("SELECT COUNT(*) FROM event_registrations WHERE su_kien_id = :su_kien_id AND email = :email");
    $stm_check->execute([
        ':su_kien_id' => $id,
        ':email' => $_SESSION['user_email']
    ]);
    $da_dang_ky = (int) $stm_check->fetchColumn() > 0;
} elseif (isset($_COOKIE['user_email'])) {
    // Hoặc kiểm tra qua cookie nếu có
    $stm_check = $ket_noi->prepare("SELECT COUNT(*) FROM event_registrations WHERE su_kien_id = :su_kien_id AND email = :email");
    $stm_check->execute([
        ':su_kien_id' => $id,
        ':email' => $_COOKIE['user_email']
    ]);
    $da_dang_ky = (int) $stm_check->fetchColumn() > 0;
}
$checkin_code = null;
$qr_checkin_url = null;
$stmReg = '';
if ($da_dang_ky) {
    // Lấy 1 bản ghi đăng ký gần nhất của user cho sự kiện này
    if (!empty($_SESSION['user_id'])) {
        $stmReg = $ket_noi->prepare("
        SELECT id, checkin_code 
        FROM event_registrations 
        WHERE su_kien_id = :sid AND user_id = :uid 
        ORDER BY id DESC LIMIT 1
      ");
        $stmReg->execute([':sid' => $id, ':uid' => $_SESSION['user_id']]);
    } else {
        // fallback theo email (session/cookie) nếu không có user_id
        $emailForFind = $_SESSION['user_email'] ?? ($_COOKIE['user_email'] ?? null);
        $stmReg = $ket_noi->prepare("
        SELECT id, checkin_code 
        FROM event_registrations 
        WHERE su_kien_id = :sid AND email = :em 
        ORDER BY id DESC LIMIT 1
      ");
        $stmReg->execute([':sid' => $id, ':em' => $emailForFind]);
    }
    $reg = $stmReg->fetch(PDO::FETCH_ASSOC);

    if ($reg) {
        $reg_id = (int) $reg['id'];
        $checkin_code = trim((string) $reg['checkin_code']);
        // Nếu chưa có checkin_code -> tạo và lưu lại
        if ($checkin_code === '') {
            $checkin_code = 'SK' . $id . '-R' . $reg_id . '-' . substr(bin2hex(random_bytes(3)), 0, 6);
            $stmUp = $ket_noi->prepare("UPDATE event_registrations SET checkin_code = :cc WHERE id = :rid");
            $stmUp->execute([':cc' => $checkin_code, ':rid' => $reg_id]);
        }

        // Tạo QR URL từ checkin_code
        $qr_checkin_url = tao_qr_qrserver($checkin_code, 260);
    }
}
// Quyết định cho phép bấm đăng ký
$cho_phep_dk = ($tt === 'sap_toi') && ($gioi_han <= 0 || $con_lai > 0) && !$da_dang_ky;
?>

<!-- Banner đỏ -->
<div class="banner-do">
    <?php $banner_su_kien = $su_kien['anh_bia'] ? $su_kien['anh_bia'] : $cfg_base_url . '/uploads/banner/banner-su-kien-8.jpg'; ?>
    <img src="<?= htmlspecialchars($banner_su_kien) ?>" alt="Anh bia" style="height:300px">
</div>

<div class="chi-tiet-su-kien-header" style="text-align:center">
    <h1><?= htmlspecialchars($su_kien['tieu_de']) ?></h1>
    <div class="nho">
        🕒 <?= htmlspecialchars($su_kien['thoi_gian_bat_dau']) ?> →
        <?= htmlspecialchars($su_kien['thoi_gian_ket_thuc']) ?>
        &nbsp; | &nbsp;
        📍 <b><?= htmlspecialchars($su_kien['dia_diem']) ?></b>
    </div>

    <!-- Nhóm chip/trạng thái/giá/số lượng -->
    <div class="trang-thai"
        style="margin-top:10px; display:flex; gap:10px; align-items:center; justify-content:center; flex-wrap:wrap">
        <!-- Chip giá -->
        <span class="chip" style="background:#e0f2fe;color:#075985;">💰 <?= htmlspecialchars($hien_gia) ?></span>

        <!-- Chip số lượng -->
        <?php if ($gioi_han > 0): ?>
        <span class="chip" style="background:#f3e8ff;color:#6b21a8;">👥 <?= $so_da_dk ?> / <?= $gioi_han ?></span>
        <?php else: ?>
        <span class="chip" style="background:#f1f5f9;color:#0f172a;">👥 <?= $so_da_dk ?> người đã đăng ký </span>
        <?php endif; ?>

        <!-- Trạng thái / nút -->
        <?php if ($da_dang_ky): ?>
        <?php if (empty($qr_checkin_url) && !empty($checkin_code)) {
                $qr_checkin_url = tao_qr_qrserver($checkin_code, 260);
            } ?>
        <button class="nut phu" type="button" data-mo-qr data-qr="<?= htmlspecialchars($qr_checkin_url) ?>"
            data-code="<?= htmlspecialchars($checkin_code) ?>">
            ✅ Đã đăng ký (xem QR)
        </button>

        <?php elseif ($cho_phep_dk): ?>
        <button class="nut chinh" data-mo-popup data-su-kien-id="<?= $su_kien['id'] ?>">✅ Đăng ký tham gia</button>
        <?php else: ?>
        <?php if ($tt === 'sap_toi' && $gioi_han > 0 && $con_lai === 0): ?>
        <span class="chip daketthuc" style="background:#fee2e2;color:#991b1b;">Đã đủ chỗ</span>
        <?php elseif ($tt === 'dang_dien_ra'): ?>
        <span class="chip dangdienra">Đang diễn ra</span>
        <?php else: ?>
        <span class="chip daketthuc">Đã kết thúc</span>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<!-- Popup QR check-in (độc lập, không dùng class popup_nen) -->
<div id="popup-qr" class="qr-overlay" style="display:none;
     position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:9999;">
    <div class="qr-box" style="max-width:520px; margin:6vh auto;
       background:#fff; border-radius:12px; padding:16px; position:relative">
        <div id="qr-noi-dung" style="text-align:center">
            <img id="qr-anh" src="<?= htmlspecialchars($qr_checkin_url) ?>" alt="QR check-in"
                style="width:460px;height:460px;border:1px solid #e2e8f0;border-radius:12px;display:block;margin:6px auto 10px">
            <div class="nho" style="font-family:ui-monospace, Menlo, monospace;">
                Mã: <b id="qr-code-text"></b>
            </div>
            <div style="margin-top:10px;display:flex;gap:8px;justify-content:center;flex-wrap:wrap">
                <a class="nut" id="qr-download" href="#" download="qr-checkin.png">⬇️ Tải QR</a>
            </div>
        </div>
    </div>
</div>
<div class="the">
    <?= $su_kien['mo_ta_html'] ?: nl2br(htmlspecialchars($su_kien['mo_ta'])) ?>
</div>
<script>
(function() {
    const overlay = document.getElementById('popup-qr');
    const box = overlay?.querySelector('.qr-box');
    const img = document.getElementById('qr-anh');
    const codeEl = document.getElementById('qr-code-text');
    const dl = document.getElementById('qr-download');
    const copyBtn = document.getElementById('qr-copy');

    // Mở popup QR
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('[data-mo-qr]');
        if (!btn) return;
        e.preventDefault();
        e.stopPropagation(); // chặn nổi bọt
        if (!overlay) return;

        const url = btn.getAttribute('data-qr') || '';
        const code = btn.getAttribute('data-code') || '';

        if (img) img.src = url;
        if (codeEl) codeEl.textContent = code;
        if (dl) dl.href = url;

        overlay.style.display = 'block';
        document.body.style.overflow = 'hidden'; // khóa scroll nền
    }, /*useCapture*/ true); // 👈 chạy trước các handler khác

    // Chặn mọi handler đóng chung khi click bên trong popup (capture + stopImmediatePropagation)
    overlay?.addEventListener('click', function(e) {
        // Click ra nền đen thì mới đóng
        if (e.target === overlay) {
            overlay.style.display = 'none';
            document.body.style.overflow = '';
        }
        // Dù click ở đâu, chặn các handler đóng popup khác can thiệp
        e.stopPropagation();
        e.stopImmediatePropagation();
    }, true); // 👈 capture

    // Ngăn click trong box "lọt" ra overlay gây đóng
    box?.addEventListener('click', function(e) {
        e.stopPropagation();
        e.stopImmediatePropagation();
    }, true);

    // Nút đóng
    document.addEventListener('click', function(e) {
        if (e.target.matches('[data-dong-qr]')) {
            e.preventDefault();
            overlay.style.display = 'none';
            document.body.style.overflow = '';
            e.stopPropagation();
            e.stopImmediatePropagation();
        }
    }, true);

    // ESC để đóng
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && overlay?.style.display === 'block') {
            overlay.style.display = 'none';
            document.body.style.overflow = '';
            e.stopPropagation();
            e.stopImmediatePropagation();
        }
    }, true);

    // Copy mã
    copyBtn?.addEventListener('click', function(e) {
        e.stopPropagation();
        e.stopImmediatePropagation();
        const t = codeEl?.textContent || '';
        if (t) navigator.clipboard.writeText(t).then(() => alert('Đã copy mã check-in'));
    }, true);
})();
</script>

<!-- Popup đăng ký sự kiện -->
<!-- Popup đăng ký sự kiện -->
<style>
.buoc {
    display: none
}

.buoc.hien {
    display: block
}

.nut.chinh.loading {
    position: relative;
    pointer-events: none;
    opacity: .8
}

.nut.chinh.loading::after {
    content: "";
    position: absolute;
    right: 12px;
    top: 50%;
    width: 16px;
    height: 16px;
    margin-top: -8px;
    border: 2px solid #fff;
    border-top-color: transparent;
    border-radius: 50%;
    animation: xoay .6s linear infinite;
}

@keyframes xoay {
    to {
        transform: rotate(360deg)
    }
}

.qr-box {
    display: flex;
    gap: 16px;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap
}

.qr-box img {
    width: 230px;
    height: 230px;
    object-fit: contain;
    border: 1px dashed #cbd5e1;
    border-radius: 12px;
    background: #fff
}

.nho-xam {
    font-size: 13px;
    color: #475569
}
</style>

<div class="popup_nen">
    <div class="popup_hop">
        <div class="hang" style="justify-content:space-between;align-items:center">
            <h3 style="margin:0">Đăng ký tham gia sự kiện</h3>
            <button class="nut" data-dong-popup>✖</button>
        </div>

        <!-- BƯỚC 1: NHẬP THÔNG TIN -->
        <div class="buoc buoc-1 hien">
            <form id="form_info">
                <input type="hidden" name="su_kien_id" value="<?= $su_kien['id'] ?>">
                <label>Họ tên</label>
                <input type="text" name="ho_ten" required>
                <label>Email</label>
                <input type="email" name="email" required>
                <label>Số điện thoại</label>
                <input type="text" name="so_dien_thoai">

                <div class="hang" style="justify-content:flex-end;gap:10px;margin-top:10px">
                    <button type="button" class="nut chinh" id="btn-tao-qr" <?= $cho_phep_dk ? '' : 'disabled' ?>>➡️
                        Tiếp tục thanh
                        toán</button>
                </div>
            </form>
            <div class="nho-xam" style="margin-top:8px">Sau khi bấm "Tiếp tục thanh toán", hệ thống sẽ hiển thị mã QR
                với số
                tiền tương ứng.</div>
        </div>

        <!-- BƯỚC 2: HIỂN THỊ QR + XÁC NHẬN -->
        <div class="buoc buoc-2">
            <div class="qr-box" style="margin:10px 0 6px">
                <div>
                    <div class="nho-xam" style="margin-bottom:6px">Quét QR để thanh toán</div>
                    <img id="qr-img" alt="QR thanh toán">
                </div>
                <div>
                    <div class="nho-xam">Số tiền</div>
                    <div id="qr-amount" style="font-size:22px;font-weight:800"></div>
                    <div class="nho-xam" style="margin-top:10px">Nội dung chuyển khoản</div>
                    <div id="qr-noidung" style="font-weight:700"></div>
                </div>
            </div>

            <div class="hang" style="justify-content:flex-end;gap:10px;margin-top:14px">
                <button type="button" class="nut phu" id="btn-quay-lai">⬅️ Quay lại</button>
                <button type="button" class="nut chinh" id="btn-xac-nhan">✅ Tôi đã thanh toán</button>
            </div>

            <div class="nho-xam" style="margin-top:6px">
                Sau khi bấm "Tôi đã thanh toán", hệ thống sẽ ghi nhận đăng ký và gửi email xác nhận.
            </div>
        </div>

    </div>
</div>

<script>
(function() {
    const popup = document.querySelector('.popup_nen');
    const b1 = document.querySelector('.buoc-1');
    const b2 = document.querySelector('.buoc-2');
    const fInfo = document.getElementById('form_info');
    const btnQR = document.getElementById('btn-tao-qr');
    const btnBack = document.getElementById('btn-quay-lai');
    const btnOK = document.getElementById('btn-xac-nhan');

    const qrImg = document.getElementById('qr-img');
    const qrAmountEl = document.getElementById('qr-amount');
    const qrNdEl = document.getElementById('qr-noidung');

    function showStep(step) {
        b1.classList.toggle('hien', step === 1);
        b2.classList.toggle('hien', step === 2);
    }

    // Tạo QR (không insert DB), chỉ dựng dữ liệu và trả URL ảnh QR
    btnQR?.addEventListener('click', async () => {
        if (!fInfo.reportValidity()) return;

        // chống double
        if (btnQR.classList.contains('loading')) return;
        btnQR.classList.add('loading');
        btnQR.textContent = '⏳ Đang tạo QR...';
        btnQR.disabled = true;

        try {
            const data = new FormData(fInfo);
            const res = await fetch('<?= $cfg_base_url ?>/public/tao_qr.php', {
                method: 'POST',
                body: data,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const kq = await res.json();
            if (!kq.ok) throw new Error(kq.msg || 'Không tạo được QR');

            // Đổ dữ liệu ra màn hình
            qrImg.src = kq.qr_img;
            qrAmountEl.textContent = kq.amount_text;
            qrNdEl.textContent = kq.noi_dung;

            // Lưu tạm payload để gửi kèm khi xác nhận
            btnOK.dataset.nd = kq.noi_dung;
            btnOK.dataset.amt = kq.amount;
            btnOK.dataset.ref = kq.tham_chieu || '';

            showStep(2);
        } catch (err) {
            alert('Lỗi tạo QR: ' + err.message);
        } finally {
            btnQR.classList.remove('loading');
            btnQR.textContent = '➡️ Tiếp tục thanh toán';
            btnQR.disabled = false;
        }
    });

    btnBack?.addEventListener('click', () => showStep(1));

    // Xác nhận đã thanh toán -> mới insert đăng ký (gọi dang_ky.php như cũ + kèm thông tin thanh toán)
    btnOK?.addEventListener('click', async () => {
        if (btnOK.classList.contains('loading')) return;
        btnOK.classList.add('loading');
        btnOK.textContent = '⏳ Đang xác nhận...';
        btnOK.disabled = true;

        try {
            const data = new FormData(fInfo);
            data.append('pay_noi_dung', btnOK.dataset.nd || '');
            data.append('pay_amount', btnOK.dataset.amt || '');
            data.append('pay_ref', btnOK.dataset.ref || '');

            const res = await fetch('<?= $cfg_base_url ?>/public/dang_ky.php', {
                method: 'POST',
                body: data,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const txt = await res.text();
            let kq;
            try {
                kq = JSON.parse(txt)
            } catch {
                throw new Error(txt)
            }

            if (kq.thanh_cong) {
                alert(kq.thong_bao || 'Đăng ký thành công!');
                popup.style.display = 'none';
                // reload để cập nhật trạng thái
                setTimeout(() => location.reload(), 600);
            } else {
                alert(kq.thong_bao || 'Có lỗi xảy ra!');
                btnOK.classList.remove('loading');
                btnOK.textContent = '✅ Tôi đã thanh toán';
                btnOK.disabled = false;
            }
        } catch (err) {
            alert('Lỗi: ' + err.message);
            btnOK.classList.remove('loading');
            btnOK.textContent = '✅ Tôi đã thanh toán';
            btnOK.disabled = false;
        }
    });

    // đóng popup
    document.querySelector('[data-dong-popup]')?.addEventListener('click', () => popup.style.display = 'none');

})();
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>