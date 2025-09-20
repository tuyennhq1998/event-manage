<?php
require_once __DIR__ . '/../functions.php';
bat_buoc_admin();
header('Content-Type: text/plain; charset=utf-8');

$hanh_dong = $_POST['hanh_dong'] ?? '';
$id        = (int)($_POST['id'] ?? 0);

if ($id<=0){ http_response_code(400); echo 'id khong hop le'; exit; }

if ($hanh_dong==='xu_ly'){
  cap_nhat_trang_thai_lh($id, 'da_xu_ly');
  echo 'ok'; exit;
}
if ($hanh_dong==='xoa'){
  xoa_lien_he($id);
  echo 'ok'; exit;
}

http_response_code(400);
echo 'hanh dong khong hop le';
