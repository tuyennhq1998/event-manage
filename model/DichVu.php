<?php
// classes/SuKien.php
// Lớp gom tất cả xử lý liên quan Sự kiện (events) + 1 số helper đăng ký

class SuKien {
    /** @var PDO */
    private $db;

    /**
     * @param PDO $db Kết nối PDO hiện có (dùng $ket_noi)
     */
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    function tinh_trang_thai_su_kien($bat_dau, $ket_thuc) {
        // Trả về 'sap_toi' | 'dang_dien_ra' | 'da_ket_thuc'
        $now = new DateTime();
        $bd = new DateTime($bat_dau);
        $kt = new DateTime($ket_thuc);
        if ($now < $bd) return 'sap_toi';
        if ($now >= $bd && $now <= $kt) return 'dang_dien_ra';
        return 'da_ket_thuc';
    }
    
    function lay_su_kien_theo_id($id) {
        global $ket_noi;
        $stm = $ket_noi->prepare('SELECT * FROM events WHERE id = ?');
        $stm->execute([$id]);
        return $stm->fetch(PDO::FETCH_ASSOC);
    }
    
    function them_su_kien($tieu_de, $mo_ta, $dia_diem,$gia, $soluong, $bat_dau, $ket_thuc, $mo_ta_html=null, $anh_bia=null){
        global $ket_noi;
        $sql = "INSERT INTO events (tieu_de, mo_ta, dia_diem,gia, so_luong, thoi_gian_bat_dau, thoi_gian_ket_thuc, mo_ta_html, anh_bia)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stm = $ket_noi->prepare($sql);
        $stm->execute([$tieu_de, $mo_ta, $dia_diem, $gia, $soluong, $bat_dau, $ket_thuc, $mo_ta_html, $anh_bia]);
      }
      
      function cap_nhat_su_kien($id, $tieu_de, $mo_ta, $dia_diem, $gia, $soluong, $bat_dau, $ket_thuc, $mo_ta_html=null, $anh_bia=null){
        global $ket_noi;
        $sql = "UPDATE events SET tieu_de=?, mo_ta=?, dia_diem=?, gia=?, so_luong=?, thoi_gian_bat_dau=?, thoi_gian_ket_thuc=?, mo_ta_html=?, anh_bia=? WHERE id=?";
        $stm = $ket_noi->prepare($sql);
        $stm->execute([$tieu_de, $mo_ta, $dia_diem, $gia, $soluong, $bat_dau, $ket_thuc, $mo_ta_html, $anh_bia, $id]);
      }
      
    function xoa_su_kien($id) {
        global $ket_noi;
        $stm = $ket_noi->prepare('DELETE FROM events WHERE id = ?');
        return $stm->execute([$id]);
    }
    }
