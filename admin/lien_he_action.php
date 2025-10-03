<?php
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../model/User.php';
require_once __DIR__ . '/../model/Contact.php';

$user = new User($ket_noi);
$contact = new Contact($ket_noi);

$user->bat_buoc_admin();

$hanh_dong = $_POST['hanh_dong'] ?? '';
$id        = (int)($_POST['id'] ?? 0);

if ($id<=0){ http_response_code(400); exit('id khong hop le'); }

if ($hanh_dong==='xu_ly'){
  $contact->cap_nhat_trang_thai_lh($id, 'da_xu_ly');
  exit('ok');
}
if ($hanh_dong==='xoa'){
  $contact->xoa_lien_he($id);
  exit('ok');
}

http_response_code(400);
exit('hanh dong khong hop le');
