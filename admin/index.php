<?php
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../model/User.php';
$user = new User($ket_noi);

$user->bat_buoc_admin();

include __DIR__ . '/../layout/header.php';
$base = rtrim($cfg_base_url, '/');
?>
<h2 style="text-align:center">Admin - Bảng điều khiển</h2>

<div class="admin-tabs">
  <div class="admin-tab su-kien active" data-tab="su-kien">Quản lý sự kiện</div>
  <div class="admin-tab bao-cao" data-tab="bao-cao">Báo cáo tham gia</div>
  <div class="admin-tab user" data-tab="user">Quản lý user</div>
  <div class="admin-tab cai-dat" data-tab="cai-dat">Cài đặt</div>
  <div class="admin-tab lien-he" data-tab="lien-he">Liên hệ</div>
</div>

<div class="admin-noi-dung" id="noi-dung-admin">
  <div class="nho">Đang tải...</div>
</div>

<script>
const BASE = "<?= rtrim($cfg_base_url,'/') ?>";

async function loadAdminTab(key, page=1){
  const box = document.getElementById('noi-dung-admin');

  // đọc per_page & q đang có trong nội dung tab hiện tại (nếu có)
  const perEl = box.querySelector('.phan-trang');
  const perPage = perEl ? (perEl.dataset.perPage || 10) : 10;

  const form = box.querySelector('.form-tim');
  const q = form ? (form.querySelector('input[name="q"]').value || '') : '';

  const urlMap = {
    'su-kien': `${BASE}/admin/tab_su_kien.php?page=${page}&per_page=${perPage}&q=${encodeURIComponent(q)}`,
    'user':    `${BASE}/admin/tab_user.php?page=${page}&per_page=${perPage}&q=${encodeURIComponent(q)}`,
    'bao-cao': `${BASE}/admin/tab_bao_cao.php?page=${page}&per_page=${perPage}&q=${encodeURIComponent(q)}`,
    'cai-dat':  `${BASE}/admin/tab_cai_dat.php`,
    'lien-he':  `${BASE}/admin/tab_lien_he.php`,
  };

  // active UI
  document.querySelectorAll('.admin-tab').forEach(t=>{
    t.classList.toggle('active', t.dataset.tab===key);
  });

  box.innerHTML = '<div class="nho">Đang tải...</div>';
  const res = await fetch(urlMap[key], { credentials:'same-origin' });
  box.innerHTML = await res.text();
}

// click tab
document.querySelectorAll('.admin-tab').forEach(t=>{
  t.addEventListener('click', ()=> loadAdminTab(t.dataset.tab, 1));
});

// phân trang — ÁP DỤNG CHUNG CHO CẢ 3 TAB (su-kien / bao-cao / user)
document.addEventListener('click', function(e){
  const a = e.target.closest('.phan-trang a.nut');
  if (!a) return;
  e.preventDefault();
  const tab = document.querySelector('.admin-tab.active')?.dataset.tab;
  if (!tab) return;
  const page = +a.dataset.page || 1;
  loadAdminTab(tab, page);
});

// submit form tìm — ÁP DỤNG CHUNG
document.addEventListener('submit', function(e){
  const form = e.target.closest('.form-tim');
  if (!form) return;
  e.preventDefault();
  const tab = document.querySelector('.admin-tab.active')?.dataset.tab;
  if (!tab) return;
  loadAdminTab(tab, 1);
});

// gõ tìm (debounce) — ÁP DỤNG CHUNG
let timer=null;
document.addEventListener('input', function(e){
  if (!e.target.matches('.form-tim input[name="q"]')) return;
  const tab = document.querySelector('.admin-tab.active')?.dataset.tab;
  if (!tab) return;
  clearTimeout(timer);
  timer = setTimeout(()=> loadAdminTab(tab, 1), 400);
});

// load mặc định
document.addEventListener('DOMContentLoaded', ()=> loadAdminTab('su-kien', 1));
</script>
<script>
(function(){
  const BASE = "<?= rtrim($cfg_base_url,'/') ?>";

  // tạo modal container 1 lần
  let modalWrap = document.getElementById('admin-modal-wrap');
  if (!modalWrap) {
    modalWrap = document.createElement('div');
    modalWrap.id = 'admin-modal-wrap';
    modalWrap.style.cssText = 'position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.45);z-index:9999;padding:16px;';
    document.body.appendChild(modalWrap);
    // click nền để đóng
    modalWrap.addEventListener('click', (e)=> {
      if (e.target === modalWrap) modalWrap.style.display = 'none';
    });
  }

  // lắng nghe click nút "Xem"
  document.addEventListener('click', async function(e){
    const btn = e.target.closest('.xem-ds');
    if (!btn) return;
    const id = btn.dataset.eventId;
    if (!id) return;

    // tải fragment danh sách user
    modalWrap.innerHTML = '<div class="the" style="padding:24px;color:#555;background:#fff">Đang tải…</div>';
    modalWrap.style.display = 'flex';
    try{
      const res = await fetch(`${BASE}/admin/bao_cao_ds_user.php?id=${encodeURIComponent(id)}`, { credentials:'same-origin' });
      const html = await res.text();
      modalWrap.innerHTML = html;
    }catch(err){
      modalWrap.innerHTML = `<div class="the" style="padding:24px;background:#fff;color:#dc2626">Lỗi tải dữ liệu: ${err.message}</div>`;
    }
  });

  // nút đóng trong modal
  document.addEventListener('click', function(e){
    if (e.target.closest('.dong-modal')) {
      modalWrap.style.display = 'none';
    }
  });
})();
</script>
<script>
(function(){
  const BASE = "<?= rtrim($cfg_base_url,'/') ?>";
  const modalWrap = document.getElementById('admin-modal-wrap') || (()=>{ 
    const m=document.createElement('div');
    m.id='admin-modal-wrap';
    m.style.cssText='position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.45);z-index:9999;padding:16px;';
    document.body.appendChild(m);
    m.addEventListener('click', e=>{ if(e.target===m) m.style.display='none'; });
    return m;
  })();

  async function loadModalList(id, page=1, perPage=10, q=''){
    const url = `${BASE}/admin/bao_cao_ds_user.php?id=${encodeURIComponent(id)}&page=${page}&per_page=${perPage}&q=${encodeURIComponent(q)}`;
    const res = await fetch(url, { credentials:'same-origin' });
    const html = await res.text();
    modalWrap.innerHTML = html;
    modalWrap.style.display = 'flex';
  }

  // mở popup khi bấm "Xem"
  document.addEventListener('click', async function(e){
    const btn = e.target.closest('.xem-ds');
    if (!btn) return;
    const id = btn.dataset.eventId;
    if (!id) return;
    modalWrap.innerHTML = '<div class="the" style="padding:20px;background:#fff">Đang tải…</div>';
    modalWrap.style.display = 'flex';
    await loadModalList(id, 1, 10, '');
  });

  // đóng modal
  document.addEventListener('click', function(e){
    if (e.target.closest('.dong-modal')) {
      modalWrap.style.display = 'none';
    }
  });

  // phân trang trong modal
  document.addEventListener('click', async function(e){
    const a = e.target.closest('.modal-phan-trang a.nut');
    if (!a) return;
    e.preventDefault();

    const root = modalWrap.querySelector('.modal-root');
    const pager = modalWrap.querySelector('.modal-phan-trang');
    if (!root || !pager) return;

    const id = root.dataset.eventId;
    const page = +a.dataset.page || 1;
    const perPage = +(pager.dataset.perPage || 10);
    const q = pager.dataset.q || '';

    await loadModalList(id, page, perPage, q);
  });

  // submit tìm kiếm trong modal
  document.addEventListener('submit', async function(e){
    const form = e.target.closest('.modal-form-tim');
    if (!form) return;
    e.preventDefault();

    const root = modalWrap.querySelector('.modal-root');
    if (!root) return;
    const id = root.dataset.eventId;
    const q = (form.querySelector('input[name="q"]')?.value || '').trim();
    const perSel = form.querySelector('select[name="per_page"]');
    const perPage = perSel ? +(perSel.value || 10) : 10;

    await loadModalList(id, 1, perPage, q);
  });

  // đổi per_page trong modal → reload trang 1
  document.addEventListener('change', async function(e){
    const sel = e.target.closest('.modal-form-tim select[name="per_page"]');
    if (!sel) return;

    const root = modalWrap.querySelector('.modal-root');
    if (!root) return;

    const id = root.dataset.eventId;
    const q = modalWrap.querySelector('.modal-form-tim input[name="q"]')?.value || '';
    const perPage = +(sel.value || 10);

    await loadModalList(id, 1, perPage, q);
  });
})();
</script>

<script>
// 2.1 Preview ảnh (giống sự kiện): input.chon-anh có data-preview
document.addEventListener('change', function(e){
  const ip = e.target.closest('input.chon-anh');
  if (!ip) return;
  const sel = ip.getAttribute('data-preview');
  const img = sel ? document.querySelector(sel) : null;
  const f = ip.files && ip.files[0];
  if (img && f) img.src = URL.createObjectURL(f);
});
</script>

<script>
// 2.2 Lưu cài đặt qua AJAX, xong nạp lại tab cài đặt (ở yên trong admin)
document.addEventListener('submit', async function(e){
  const form = e.target.closest('#form-cai-dat');
  if (!form) return;
  e.preventDefault();

  const box = document.getElementById('noi-dung-admin');
  box.innerHTML = '<div class="the">Đang lưu cài đặt…</div>';

  try{
    const res  = await fetch(form.action, { method:'POST', body:new FormData(form), credentials:'same-origin' });
    const data = await res.json();
    if (data.ok) {
      // nạp lại tab "cai-dat" (giống khi bạn lưu sự kiện xong refresh block)
      if (window.loadAdminTab) window.loadAdminTab('cai-dat', 1);
      else location.reload();
    } else {
      box.innerHTML = '<div class="the" style="background:#fee2e2;color:#991b1b">Lỗi: '+(data.err||'Không rõ')+'</div>';
    }
  }catch(err){
    box.innerHTML = '<div class="the" style="background:#fee2e2;color:#991b1b">Lỗi: '+err.message+'</div>';
  }
});
</script>
<script>
const base = "<?= $cfg_base_url ?>"; // đảm bảo có base toàn cục

// Ngăn overlay bị đóng/ reload bởi handler khác
document.addEventListener('click', async function(e){
  // ---- Xem chi tiết ----
  const btnXem = e.target.closest('.xem-lh');
  if (btnXem){
    e.preventDefault();
    e.stopPropagation(); // chặn bubble tới handler khác

    const id = btnXem.dataset.id;
    const overlay = document.getElementById('lh-overlay');
    const box = document.getElementById('lh-noi-dung');
    if (!overlay || !box) return;
    overlay.style.display='block';
    box.textContent='Đang tải…';

    try{
      // GỌI ĐÚNG TÊN FILE BẠN ĐANG CÓ:
      const res = await fetch(`${base}/admin/lien_he_xem.php?id=${encodeURIComponent(id)}`, {credentials:'same-origin'});
      const html = await res.text();
      box.innerHTML = html.trim() ? html : '<p style="color:#991b1b">Không có dữ liệu.</p>';
    }catch(err){
      box.innerHTML = `<p style="color:#991b1b">Không tải được chi tiết: ${err.message}</p>`;
    }

    return;
  }

  // ---- Đóng overlay ----
  if (e.target.matches('[data-dong-lh]')){
    e.preventDefault();
    e.stopPropagation();
    const overlay = document.getElementById('lh-overlay');
    if (overlay) overlay.style.display='none';
    return;
  }

  // Nhấp nền overlay thì KHÔNG đóng (tránh lỡ tay)
  const clickOnOverlay = e.target.classList && e.target.classList.contains('lh-overlay');
  if (clickOnOverlay){
    e.stopPropagation(); // chỉ chặn, không đóng
    return;
  }

  // ---- Đánh dấu xử lý ----
  const btnXL = e.target.closest('.xl-lh');
  if (btnXL){
    e.preventDefault(); e.stopPropagation();
    const id = btnXL.dataset.id;
    if (!confirm('Đánh dấu đã xử lý liên hệ #'+id+'?')) return;

    try{
      const res = await fetch(`${base}/admin/lien_he_action.php`, {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        credentials:'same-origin',
        body: new URLSearchParams({hanh_dong:'xu_ly', id})
      });
      if (!res.ok) throw new Error('HTTP '+res.status);
      if (typeof window.loadAdminTab==='function') window.loadAdminTab('lien-he');
    }catch(err){
      alert('Không thực hiện được: '+err.message);
    }
    return;
  }

  // ---- Xóa ----
  const btnXoa = e.target.closest('.xoa-lh');
  if (btnXoa){
    e.preventDefault(); e.stopPropagation();
    const id = btnXoa.dataset.id;
    if (!confirm('Xóa liên hệ #'+id+'?')) return;

    try{
      const res = await fetch(`${base}/admin/lien_he_action.php`, {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        credentials:'same-origin',
        body: new URLSearchParams({hanh_dong:'xoa', id})
      });
      if (!res.ok) throw new Error('HTTP '+res.status);
      if (typeof window.loadAdminTab==='function') window.loadAdminTab('lien-he');
    }catch(err){
      alert('Không thực hiện được: '+err.message);
    }
    return;
  }
});
</script>


<?php include __DIR__ . '/../layout/footer.php'; ?>
