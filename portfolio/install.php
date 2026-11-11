<?php
/* إنشاء ملف الاتصال (includes/config.php) تلقائياً إذا لم يكن موجوداً */
if (!is_file(__DIR__ . '/includes/config.php')) {
    $defaultConfig = <<<'PHP'
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'portfolio_db');
define('DB_USER', 'root');
define('DB_PASS', '');

define('APP_NAME', 'م. محمد الصواف | مهندس شبكات وأمن معلومات');
define('UPLOADS_DIR', __DIR__ . '/../uploads');
define('UPLOADS_URL', 'uploads');
define('MAX_FILE_SIZE', 2097152); // 2MB
define('ALLOWED_IMG_EXT', ['jpg', 'jpeg', 'png', 'webp', 'gif']);
define('ALLOWED_IMG_MIME', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

define('ADMIN_USERNAME_DEFAULT', 'admin');
define('ADMIN_PASSWORD_DEFAULT', 'admin123');

date_default_timezone_set('Asia/Riyadh');
PHP;
    file_put_contents(__DIR__ . '/includes/config.php', $defaultConfig);
}

require_once __DIR__ . '/includes/functions.php';

$step    = $_GET['step'] ?? 'start';
$msg     = '';
$isError = false;
$installOk = false;

$lockFile = __DIR__ . '/includes/installed.lock';
$isInstalled = file_exists($lockFile);

$pdo = null;
function dbh(): PDO {
    global $pdo;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';charset=utf8mb4';
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        } catch (PDOException $e) {
            throw new RuntimeException('تعذر الاتصال بخادم MySQL. تأكد من تشغيله وأن بيانات الاتصال في includes/config.php صحيحة.');
        }
    }
    return $pdo;
}

if ($step === 'install') {
    try {
        $force = !empty($_GET['force']);

        if ($isInstalled && !$force) {
            throw new RuntimeException('المشروع مثبّت مسبقاً.');
        }

        $sql = file_get_contents(__DIR__ . '/install.sql');
        if ($sql === false) {
            throw new RuntimeException('ملف install.sql غير موجود.');
        }

        $conn = dbh();
        $dbName = DB_NAME;

        $exists = $conn->query("SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = " . $conn->quote($dbName))->fetchColumn();
        if ($exists && $force) {
            $conn->exec("DROP DATABASE `$dbName`");
        }

        $lines = array_filter(array_map('trim', explode("\n", $sql)), function ($line) {
            return $line !== '' && !str_starts_with($line, '--');
        });
        $cleanSql = implode("\n", $lines);

        foreach (array_filter(array_map('trim', explode(';', $cleanSql))) as $stmt) {
            if ($stmt === '') {
                continue;
            }
            $conn->exec($stmt);
        }

        $defaultPassword = ADMIN_PASSWORD_DEFAULT;
        $hash = password_hash($defaultPassword, PASSWORD_BCRYPT);
        $conn->prepare(
            "INSERT INTO `$dbName`.users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, 'admin')
             ON DUPLICATE KEY UPDATE password = ?, full_name = ?, email = ?"
        )->execute([
            ADMIN_USERNAME_DEFAULT, $hash, 'مدير الموقع', 'admin@localhost',
            $hash, 'مدير الموقع', 'admin@localhost',
        ]);

        @mkdir(__DIR__ . '/uploads', 0775, true);
        file_put_contents($lockFile, date('c'));

        $installOk = true;
        $msg = 'تم تثبيت المشروع بنجاح!';
        $isInstalled = true;
    } catch (Throwable $e) {
        $isError = true;
        $msg = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تثبيت المشروع | منصة المعرض الشخصي</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Tajawal', sans-serif; background: #070b14; color: #e6edf7; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
        .wrap { width: 100%; max-width: 560px; }
        .card { background: #101828; border: 1px solid rgba(148,163,184,.15); border-radius: 20px; padding: 40px 36px; text-align: center; box-shadow: 0 20px 60px rgba(0,0,0,.5); }
        .icon-big { width: 74px; height: 74px; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; border-radius: 22px; background: linear-gradient(135deg,#22d3ee,#34d399); color: #04121a; font-size: 32px; box-shadow: 0 10px 34px rgba(34,211,238,.4); }
        h1 { font-size: 24px; font-weight: 900; margin-bottom: 8px; }
        .sub { color: #94a3b8; font-size: 14px; margin-bottom: 26px; }
        .msg-box { border-radius: 12px; padding: 16px 18px; font-size: 14px; font-weight: 600; margin-bottom: 22px; text-align: right; }
        .msg-ok { background: rgba(52,211,153,.1); border: 1px solid rgba(52,211,153,.35); color: #6ee7b7; }
        .msg-err { background: rgba(248,113,113,.1); border: 1px solid rgba(248,113,113,.35); color: #fca5a5; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 9px; padding: 14px 30px; border-radius: 12px; font-family: inherit; font-size: 15px; font-weight: 800; border: none; cursor: pointer; text-decoration: none; transition: .25s; }
        .btn-main { background: linear-gradient(135deg,#22d3ee,#34d399); color: #04121a; box-shadow: 0 8px 26px rgba(34,211,238,.3); }
        .btn-main:hover { transform: translateY(-2px); }
        .btn-ghost { background: transparent; color: #e6edf7; border: 1px solid rgba(148,163,184,.25); }
        .info { background: #0d1526; border: 1px solid rgba(148,163,184,.12); border-radius: 14px; padding: 18px 20px; margin-top: 20px; text-align: right; }
        .info h3 { font-size: 15px; font-weight: 800; margin-bottom: 10px; display: flex; align-items: center; gap: 8px; color: #22d3ee; }
        .info .row { display: flex; justify-content: space-between; font-size: 13px; padding: 7px 0; border-bottom: 1px dashed rgba(148,163,184,.12); }
        .info .row:last-child { border: none; }
        .info b { direction: ltr; font-family: 'Consolas', monospace; color: #34d399; }
        .actions { display: flex; gap: 12px; justify-content: center; margin-top: 24px; flex-wrap: wrap; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div class="icon-big"><i class="fa-solid fa-shield-halved"></i></div>
            <h1>منصة المعرض الشخصي والخدمات الرقمية</h1>
            <p class="sub">م. محمد الصواف — مهندس شبكات وأمن معلومات</p>

            <?php if ($msg): ?>
                <div class="msg-box <?= $isError ? 'msg-err' : 'msg-ok' ?>">
                    <i class="fa-solid <?= $isError ? 'fa-circle-exclamation' : 'fa-circle-check' ?>"></i> <?= e($msg) ?>
                </div>
            <?php endif; ?>

            <?php if ($installOk): ?>
                <div class="info">
                    <h3><i class="fa-solid fa-key"></i> بيانات تسجيل الدخول الافتراضية</h3>
                    <div class="row"><span>المسؤول</span><b>admin</b></div>
                    <div class="row"><span>كلمة المرور</span><b>admin123</b></div>
                </div>
                <div class="info">
                    <h3><i class="fa-solid fa-circle-info"></i> خطوات مهمة قبل النشر</h3>
                    <div class="row"><span>1. غيّر كلمة المرور</span><b>لوحة التحكم ← تغيير كلمة المرور</b></div>
                    <div class="row"><span>2. احذف هذا الملف</span><b>install.php</b></div>
                    <div class="row"><span>3. عدّل النصوص</span><b>لوحة التحكم ← الإعدادات</b></div>
                </div>
                <div class="actions">
                    <a href="index.php" class="btn btn-main"><i class="fa-solid fa-globe"></i> الذهاب للموقع</a>
                    <a href="admin/login.php" class="btn btn-ghost"><i class="fa-solid fa-user-shield"></i> لوحة التحكم</a>
                </div>
            <?php else: ?>
                <p class="sub">يقوم المثبّت بإنشاء قاعدة البيانات والتشغيل وبيانات تجريبية وحساب المدير.</p>
                <div class="actions">
                    <a href="?step=install" class="btn btn-main"><i class="fa-solid fa-rocket"></i> تثبيت الآن</a>
                    <?php if ($isInstalled): ?>
                        <a href="?step=install&force=1" class="btn btn-ghost" style="color:#fca5a5;"><i class="fa-solid fa-rotate"></i> إعادة التثبيت (مسح كامل)</a>
                    <?php endif; ?>
                </div>
                <?php if (function_exists('phpversion')): ?>
                    <p class="sub" style="margin-top:18px;font-size:12px;">PHP <?= phpversion() ?> — تحتاج إلى MySQL/MariaDB للعمل.</p>
                <?php endif; ?>
                <div class="info">
                    <h3><i class="fa-solid fa-list-check"></i> المتطلبات</h3>
                    <div class="row"><span>PHP</span><b>8.0 أو أحدث</b></div>
                    <div class="row"><span>MySQL / MariaDB</span><b>5.7 أو أحدث</b></div>
                    <div class="row"><span>إعدادات الاتصال</span><b>includes/config.php</b></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>