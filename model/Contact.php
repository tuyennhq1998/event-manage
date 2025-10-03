<?php
class Contact
{
  /** @var PDO */
  private $db;

  /**
   * @param PDO $db Kết nối PDO hiện có (dùng $ket_noi)
   */
  public function __construct(PDO $db)
  {
    $this->db = $db;
  }

  function tao_lien_he($ho_ten, $email, $so_dien_thoai, $tieu_de, $noi_dung)
  {
    global $ket_noi;
    $sql = "INSERT INTO contacts (ho_ten, email, so_dien_thoai, tieu_de, noi_dung) VALUES (?,?,?,?,?)";
    $stm = $ket_noi->prepare($sql);
    return $stm->execute([$ho_ten, $email, $so_dien_thoai, $tieu_de, $noi_dung]);
  }

  function dem_lien_he($q = '')
  {
    global $ket_noi;
    if ($q === '') {
      return (int)$ket_noi->query("SELECT COUNT(*) FROM contacts")->fetchColumn();
    }
    $stm = $ket_noi->prepare("SELECT COUNT(*) FROM contacts WHERE (ho_ten LIKE :kw OR email LIKE :kw OR tieu_de LIKE :kw)");
    $stm->execute([':kw' => "%$q%"]);
    return (int)$stm->fetchColumn();
  }

  function danh_sach_lien_he($page = 1, $per_page = 10, $q = '')
  {
    global $ket_noi;
    $offset = ($page - 1) * $per_page;
    $params = [];
    $where = '1=1';
    if ($q !== '') {
      $where .= " AND (ho_ten LIKE :kw OR email LIKE :kw OR tieu_de LIKE :kw)";
      $params[':kw'] = "%$q%";
    }
    $sql = "SELECT * FROM contacts WHERE $where ORDER BY ngay_gui DESC, id DESC LIMIT :lim OFFSET :off";
    $stm = $ket_noi->prepare($sql);
    foreach ($params as $k => $v) $stm->bindValue($k, $v);
    $stm->bindValue(':lim', (int)$per_page, PDO::PARAM_INT);
    $stm->bindValue(':off', (int)$offset, PDO::PARAM_INT);
    $stm->execute();
    return $stm->fetchAll(PDO::FETCH_ASSOC);
  }

  function lien_he_theo_id($id)
  {
    global $ket_noi;
    $stm = $ket_noi->prepare("SELECT * FROM contacts WHERE id=:id");
    $stm->execute([':id' => $id]);
    return $stm->fetch(PDO::FETCH_ASSOC);
  }

  function cap_nhat_trang_thai_lh($id, $tt = 'da_xu_ly')
  {
    global $ket_noi;
    $stm = $ket_noi->prepare("UPDATE contacts SET trang_thai=:tt WHERE id=:id");
    return $stm->execute([':tt' => $tt, ':id' => $id]);
  }

  function xoa_lien_he($id)
  {
    global $ket_noi;
    $stm = $ket_noi->prepare("DELETE FROM contacts WHERE id=:id");
    return $stm->execute([':id' => $id]);
  }
}