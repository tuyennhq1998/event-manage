<?php
// index.php ở THƯ MỤC GỐC (cùng cấp với folder public)
// Mục tiêu: ẩn /public khỏi URL, nhưng thực tế phục vụ file trong ./public

$publicDir = __DIR__ . '/public';
$publicReal = realpath($publicDir);
if ($publicReal === false) {
    http_response_code(500);
    exit('Public folder not found.');
}

// Lấy path sạch từ URL, tránh query string
$uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

// Chuẩn hoá: chống leo thư mục
$targetPath = realpath($publicReal . $uriPath);

// Nếu trùng file thật trong public => trả file tĩnh
if ($targetPath !== false
    && strncmp($targetPath, $publicReal, strlen($publicReal)) === 0
    && is_file($targetPath)) {

    // Gán Content-Type đơn giản
    $mime = function_exists('mime_content_type') ? mime_content_type($targetPath) : 'application/octet-stream';
    header('Content-Type: ' . $mime);

    // Cache nhẹ cho static
    if (preg_match('~\.(css|js|png|jpe?g|gif|svg|webp|ico|ttf|woff2?)$~i', $targetPath)) {
        header('Cache-Control: public, max-age=31536000, immutable');
    }

    readfile($targetPath);
    exit;
}

// Không phải file thật => giao cho public/index.php xử lý (router của app)
chdir($publicReal);

// Sửa một số biến môi trường để app “nghĩ” nó đang chạy từ /public
$_SERVER['SCRIPT_FILENAME'] = $publicReal . '/index.php';
$_SERVER['SCRIPT_NAME']     = '/index.php';
$_SERVER['PHP_SELF']        = '/index.php';

require $publicReal . '/index.php';
