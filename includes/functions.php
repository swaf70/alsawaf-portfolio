<?php
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            die('تعذر الاتصال بقاعدة البيانات. تأكد من تشغيل MySQL وتعديل بيانات الاتصال في includes/config.php');
        }
    }
    return $pdo;
}

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function base_url(string $path = ''): string {
    $doc  = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
    $dir  = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    $root = $dir;
    for ($i = 0; $i < 8; $i++) {
        $marker = str_replace('//', '/', $doc . $root . '/includes/config.php');
        if (is_file($marker)) {
            break;
        }
        $parent = dirname($root);
        if ($parent === $root || $parent === '.' || $parent === '\\' || $parent === '/') {
            $root = '/';
            break;
        }
        $root = $parent;
    }
    $root = rtrim($root, '/');
    return $root . ($path !== '' ? '/' . ltrim($path, '/') : '');
}

function setting(string $key, ?string $default = null): ?string {
    static $cache = null;
    if ($cache === null) {
        try {
            $rows = db()->query('SELECT setting_key, setting_value FROM settings')->fetchAll();
            $cache = [];
            foreach ($rows as $row) {
                $cache[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Throwable $e) {
            $cache = [];
        }
    }
    return $cache[$key] ?? $default;
}

function category_name(int $id, ?string $default = null): ?string {
    static $cache = null;
    if ($cache === null) {
        try {
            $cache = db()->query('SELECT id, name FROM categories')->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Throwable $e) {
            $cache = [];
        }
    }
    return $cache[$id] ?? $default;
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(?string $token): bool {
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}

function flash(string $type, string $message): void {
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes(): array {
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function current_user(): ?array {
    static $u = false;
    if ($u === false) {
        if (!isset($_SESSION['user_id'])) {
            $u = null;
        } else {
            try {
                $stmt = db()->prepare(
                    'SELECT id, username, full_name, email, phone, role, created_at, last_login
                     FROM users WHERE id = ? LIMIT 1'
                );
                $stmt->execute([(int)$_SESSION['user_id']]);
                $u = $stmt->fetch() ?: null;
            } catch (Throwable $e) {
                $u = null;
            }
            if (!$u) {
                session_regenerate_id(true);
                unset($_SESSION['user_id']);
            }
        }
    }
    return $u;
}

function login_user(int $id): void {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $id;
    try {
        db()->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')->execute([$id]);
    } catch (Throwable $e) {
        // تجاهل فشل التحديث
    }
}

function logout_user(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function require_login(): void {
    if (!current_user()) {
        redirect(base_url('login.php'));
    }
}

function require_admin(): void {
    $u = current_user();
    if (!$u || ($u['role'] ?? 'user') !== 'admin') {
        redirect(base_url('login.php'));
    }
}

function is_admin(): bool {
    $u = current_user();
    return $u !== null && ($u['role'] ?? 'user') === 'admin';
}

function upload_image(array $file, string $subdir = ''): string {
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new RuntimeException('بيانات الملف غير صالحة');
    }

    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        throw new RuntimeException('no_file');
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('حدث خطأ أثناء رفع الملف. حاول مرة أخرى.');
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        throw new RuntimeException('حجم الصورة يتجاوز الحد المسموح (2MB).');
    }

    $mimes = [];
    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimes[] = $finfo->file($file['tmp_name']);
    }
    $size = @getimagesize($file['tmp_name']);
    if ($size !== false && isset($size['mime'])) {
        $mimes[] = $size['mime'];
    }
    $ok = array_intersect($mimes, ALLOWED_IMG_MIME);
    if (!$ok) {
        throw new RuntimeException('نوع الملف غير مسموح. يُقبل فقط صور JPG, PNG, WebP, GIF.');
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_IMG_EXT, true)) {
        $detected = $mimes[0] ?? '';
        $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'][$detected] ?? 'png';
    }

    $dir = UPLOADS_DIR;
    if ($subdir !== '') {
        $dir .= '/' . trim($subdir, '/');
    }
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $target   = $dir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new RuntimeException('تعذر حفظ الملف على الخادم.');
    }

    $rel = 'uploads';
    if ($subdir !== '') {
        $rel .= '/' . trim($subdir, '/');
    }
    return $rel . '/' . $filename;
}

function delete_upload(string $path): void {
    if ($path === '') {
        return;
    }
    $full = __DIR__ . '/../' . $path;
    if (is_file($full)) {
        @unlink($full);
    }
}

function safe_delete(string $condition, array $params, string $table, string $redirectUrl) {
    try {
        db()->beginTransaction();
        $stmt = db()->prepare("SELECT * FROM $table WHERE $condition");
        $stmt->execute($params);
        $row = $stmt->fetch();
        if ($row) {
            if (isset($row['image']) && $row['image']) {
                delete_upload($row['image']);
            }
            db()->prepare("DELETE FROM $table WHERE $condition")->execute($params);
        }
        db()->commit();
        flash('success', 'تم الحذف بنجاح.');
    } catch (Throwable $e) {
        db()->rollBack();
        flash('error', 'تعذر الحذف: ' . $e->getMessage());
    }
    redirect($redirectUrl);
}