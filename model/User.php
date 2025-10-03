<?php
class User
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

    function tim_user_theo_email($email)
    {
        global $ket_noi;
        $sql = 'SELECT * FROM users WHERE email = ? LIMIT 1';
        $stm = $ket_noi->prepare($sql);
        $stm->execute([$email]);
        return $stm->fetch(PDO::FETCH_ASSOC);
    }
    function tao_user($ten, $email, $mat_khau)
    {
        global $ket_noi;
        $hash = password_hash($mat_khau, PASSWORD_BCRYPT);
        $sql = 'INSERT INTO users (ten, email, mat_khau_hash) VALUES (?, ?, ?)';
        $stm = $ket_noi->prepare($sql);
        $stm->execute([$ten, $email, $hash]);
        return $ket_noi->lastInsertId();
    }

    function dang_nhap($email, $mat_khau)
    {
        $user = $this->tim_user_theo_email($email);
        if ($user && password_verify($mat_khau, $user['mat_khau_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_ten'] = $user['ten'];
            $_SESSION['user_vai_tro'] = $user['vai_tro'];
            return true;
        }
        return false;
    }

    function bat_buoc_dang_nhap()
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: /event-manage/public/dang_nhap.php');
            exit;
        }
    }

    function bat_buoc_admin()
    {
        $this->bat_buoc_dang_nhap();
        if (($_SESSION['user_vai_tro'] ?? 'user') !== 'admin') {
            die('Bạn không có quyền truy cập trang này');
        }
    }
}