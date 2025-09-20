// JS đơn giản để mở/đóng popup và gửi AJAX đăng ký sự kiện


// Mở popup đăng ký (được gắn qua data-*)
document.addEventListener('click', function(e){
   
    
    

    });
    
    
    const formDangKy = document.getElementById('form_dang_ky_su_kien');
    if (formDangKy) {
      formDangKy.addEventListener('submit', async function(ev){
        ev.preventDefault();
        try {
          const du_lieu = new FormData(formDangKy);
          const res = await fetch(formDangKy.action, { method:'POST', body: du_lieu });
          const text = await res.text();                   // đọc thô
          let kq;
          try { kq = JSON.parse(text); }                   // thử parse JSON
          catch { throw new Error('Server tra ve khong phai JSON:\n' + text); }
          if (!res.ok || !kq) throw new Error('Loi ' + res.status);
          alert(kq.thong_bao || 'Dang ky thanh cong!');
          if (kq.thanh_cong) {
            const nen = document.querySelector('.popup_nen');
            if (nen) nen.style.display = 'none';
            formDangKy.reset();
          }
        } catch (err) {
          console.error(err);
          alert('Dang ky that bai: ' + err.message);
        }
      });
    }

    // === Tab lọc danh sách trên trang chủ ===
(function(){
    const tabs = document.querySelectorAll('.tabbar .tab');
    const nhoms = document.querySelectorAll('.thu_vien[data-nhom]');
    if (!tabs.length || !nhoms.length) return;
  
    function chon(nhom) {
      tabs.forEach(t => t.classList.toggle('active', t.dataset.nhom === nhom));
      nhoms.forEach(el => el.classList.toggle('hidden', el.dataset.nhom !== nhom));
    }
  
    tabs.forEach(t => t.addEventListener('click', () => chon(t.dataset.nhom)));
    // Mặc định chọn "sap_toi"
    chon('sap_toi');
  })();
  // Popup đăng ký
document.addEventListener('click', e=>{
    // Mở popup
    const nutMo = e.target.closest('[data-mo-popup]');
    if (nutMo) {
      const nen = document.querySelector('.popup_nen');
      if (nen){
        nen.classList.add('hien');
        nen.querySelector('[name="su_kien_id"]').value = nutMo.dataset.suKienId;
      }
    }
    // Đóng popup
    if (e.target.matches('[data-dong-popup]') || e.target.classList.contains('popup_nen')){
      const nen = document.querySelector('.popup_nen');
      if (nen) nen.classList.remove('hien');
    }
  });
  




  // Admin dashboard tabs
const tabs = document.querySelectorAll('.admin-tab');
const box = document.getElementById('noi-dung-admin');
if (tabs.length && box){
  tabs.forEach(tab=>{
    tab.addEventListener('click', ()=>{
      tabs.forEach(t=>t.classList.remove('active'));
      tab.classList.add('active');
      const loai = tab.dataset.tab;
      if (loai==='su-kien'){
        box.innerHTML = '<h3>Quản lý sự kiện</h3><p>Đây là khu vực quản lý, thêm, sửa, xóa sự kiện.</p>';
      }else if(loai==='bao-cao'){
        box.innerHTML = '<h3>Báo cáo tham gia</h3><p>Xem danh sách người tham gia theo sự kiện.</p>';
      }else{
        box.innerHTML = '<h3>Quản lý user</h3><p>Quản lý danh sách người dùng, phân quyền, xóa tài khoản.</p>';
      }
    });
  });
}


// ========== HOME (index) tabs + lazy load ==========
(function(){
    const home = document.getElementById('home-page');
    if (!home) return; // chỉ chạy ở index
  
    const BASE = home.dataset.baseUrl || '';
    const PER  = 3;
    const pages = { 'sap_toi': 1, 'dang_dien_ra': 1, 'da_ket_thuc': 1 };
  
    // Tabs
    const tabs = home.querySelectorAll('.home-tabs .tab');
    const panes = home.querySelectorAll('.home-pane');
    function showPane(nhom){
      tabs.forEach(t=>t.classList.toggle('active', t.dataset.nhom===nhom));
      panes.forEach(p=>p.classList.toggle('active', p.dataset.nhom===nhom));
    }
    tabs.forEach(t=> t.addEventListener('click', ()=> showPane(t.dataset.nhom)));
    showPane('sap_toi');
  
    // Lazy load
   // ===== Lazy load (dùng ajax_more.php + offset/limit + giữ filter) =====
(async function(){
  const home = document.getElementById('home-page');
  if (!home) return;

  const BASE = home.dataset.baseUrl || '';
  const PER  = 3;

  async function loadMore(nhom, btn){
    const grid = home.querySelector(`.home-grid[data-nhom="${nhom}"]`);
    if (!grid) return;

    // offset = số item hiện có trong grid
    const offset = grid.querySelectorAll('.o-anh').length;

    // giữ các tham số lọc từ data-attributes của nút
    const q   = btn.dataset.q || '';
    const tu  = btn.dataset.tu || '';
    const den = btn.dataset.den || '';

    btn.disabled = true;
    const oldText = btn.textContent;
    btn.textContent = 'Đang tải...';

    try {
      const url = `${BASE}/public/ajax_more.php`
        + `?nhom=${encodeURIComponent(nhom)}`
        + `&offset=${offset}`
        + `&limit=${PER}`
        + `&q=${encodeURIComponent(q)}`
        + `&tu_ngay=${encodeURIComponent(tu)}`
        + `&den_ngay=${encodeURIComponent(den)}`;

      const res  = await fetch(url, { credentials: 'same-origin' });
      const html = await res.text();
      const trimmed = html.trim();

      if (!trimmed) {
        btn.textContent = 'Hết dữ liệu';
        btn.disabled = true;
        return;
      }

      const wrap = document.createElement('div');
      wrap.innerHTML = trimmed;
      // chỉ append các thẻ .o-anh (tránh rác/space text node)
      wrap.querySelectorAll('.o-anh').forEach(el => grid.appendChild(el));

      // Nếu trả về ít hơn PER thì coi như hết
      const added = wrap.querySelectorAll('.o-anh').length;
      if (added < PER) {
        btn.textContent = 'Hết dữ liệu';
        btn.disabled = true;
      } else {
        btn.textContent = 'Xem thêm';
        btn.disabled = false;
      }
    } catch (e) {
      console.error(e);
      btn.textContent = 'Lỗi, thử lại';
      btn.disabled = false;
    }
  }

  home.addEventListener('click', function(e){
    const btn = e.target.closest('.home-see-more .btn-more');
    if (!btn) return;
    loadMore(btn.dataset.nhom, btn);
  });
})();

  })();
  