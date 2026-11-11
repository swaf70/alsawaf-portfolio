<?php
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    $me = current_user();
    redirect(is_admin() ? base_url('admin/index.php') : base_url('index.php'));
}

$errors   = [];
$oldName  = trim($_POST['name'] ?? '');
$oldEmail = trim($_POST['email'] ?? '');
$oldPhone = trim($_POST['phone'] ?? '');

$defaultTab = 'login';
$flashes = get_flashes();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';

    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'انتهت صلاحية الجلسة، أعد المحاولة.';
    } elseif ($action === 'login') {
        $identifier = trim($_POST['identifier'] ?? '');
        $password   = $_POST['password'] ?? '';

        if ($identifier === '' || $password === '') {
            $errors[] = 'يرجى إدخال البريد الإلكتروني أو اسم المستخدم وكلمة المرور.';
        } else {
            try {
                $stmt = db()->prepare('SELECT * FROM users WHERE email = ? OR username = ? LIMIT 1');
                $stmt->execute([$identifier, $identifier]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    login_user((int)$user['id']);
                    redirect(
                        $user['role'] === 'admin'
                            ? base_url('admin/index.php')
                            : base_url('index.php')
                    );
                } else {
                    $errors[] = 'بيانات الدخول غير صحيحة، تأكد من البريد الإلكتروني وكلمة المرور.';
                }
            } catch (Throwable $e) {
                $errors[] = 'تعذر الاتصال بقاعدة البيانات. تأكد من تشغيل MySQL وتنفيذ install.php.';
            }
        }
    } elseif ($action === 'register') {
        $defaultTab = 'register';
        $name    = trim($_POST['name'] ?? '');
        $email   = mb_strtolower(trim($_POST['email'] ?? ''));
        $phone   = trim($_POST['phone'] ?? '');
        $pass    = $_POST['password'] ?? '';
        $pass2   = $_POST['password2'] ?? '';

        if (mb_strlen($name) < 2) {
            $errors[] = 'الاسم الكامل يجب أن يكون حرفين على الأقل.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'يرجى إدخال بريد إلكتروني صحيح.';
        }
        if ($phone !== '' && !preg_match('/^[0-9+ ()-]{7,20}$/', $phone)) {
            $errors[] = 'رقم الهاتف غير صحيح.';
        }
        if (mb_strlen($pass) < 6) {
            $errors[] = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل.';
        }
        if ($pass !== $pass2) {
            $errors[] = 'تأكيد كلمة المرور غير مطابق.';
        }

        if (empty($errors)) {
            try {
                $exists = db()->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
                $exists->execute([$email]);
                if ((int)$exists->fetchColumn() > 0) {
                    $errors[] = 'هذا البريد الإلكتروني مسجّل مسبقاً، سجّل الدخول مباشرة.';
                } else {
                    $stmt = db()->prepare(
                        'INSERT INTO users (username, full_name, email, phone, password, role)
                         VALUES (?, ?, ?, ?, ?, ?)'
                    );
                    $stmt->execute([
                        '', $name, $email, $phone !== '' ? $phone : null,
                        password_hash($pass, PASSWORD_BCRYPT), 'user',
                    ]);
                    login_user((int)db()->lastInsertId());
                    redirect(base_url('index.php'));
                }
            } catch (Throwable $e) {
                $errors[] = 'تعذر إنشاء الحساب. تأكد من عمل قاعدة البيانات.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول | <?= e(setting('site_name', APP_NAME)) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php if (defined('INLINE_ASSETS')): ?>
        <style id="app-inline-login-css"><?= $INLINE['css_login'] ?? '' ?></style>
    <?php else: ?>
        <link rel="stylesheet" href="<?= e(base_url('assets/css/login.css?v=2')) ?>">
    <?php endif; ?>
</head>
<body class="gate-body">

<div class="gate-bg" aria-hidden="true">
    <div class="gate-blob gb1"></div>
    <div class="gate-blob gb2"></div>
    <div class="gate-grid"></div>
</div>

<div class="gate-wrap">
    <div class="gate-card">
        <div class="gate-head">
            <span class="gate-logo"><i class="fa-solid fa-shield-halved"></i></span>
            <h1 class="gate-title">مرحباً بعودتك</h1>
            <p class="gate-sub"><?= e(setting('site_name', 'منصة الأعمال')) ?> — سجّل دخولك لمشاهدة تفاصيل المشاريع</p>
        </div>

        <?php if ($flashes): ?>
            <div class="gate-flashes">
                <?php foreach ($flashes as $f): ?>
                    <div class="gate-flash gate-flash-<?= e($f['type']) ?>">
                        <i class="fa-solid <?= $f['type'] === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check' ?>"></i>
                        <?= e($f['message']) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="gate-alert">
                <?php foreach ($errors as $er): ?>
                    <div><i class="fa-solid fa-circle-exclamation"></i> <?= e($er) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="tabs">
            <input type="radio" name="gtab" id="gtab-login" class="tab-input" <?= $defaultTab === 'login' ? 'checked' : '' ?>>
            <input type="radio" name="gtab" id="gtab-register" class="tab-input" <?= $defaultTab === 'register' ? 'checked' : '' ?>>
            <nav class="tab-nav">
                <label for="gtab-login" class="tab-label">تسجيل الدخول</label>
                <label for="gtab-register" class="tab-label">حساب جديد</label>
            </nav>

            <section class="tab-panel panel-login">
                <form method="post" action="" class="gate-form" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="login">
                    <div class="form-group">
                        <label for="gIdentifier"><i class="fa-solid fa-envelope"></i> البريد الإلكتروني</label>
                        <input type="text" id="gIdentifier" name="identifier" class="form-control" placeholder="name@example.com" dir="ltr" required autofocus>
                    </div>
                    <div class="form-group">
                        <label for="gPassword"><i class="fa-solid fa-lock"></i> كلمة المرور</label>
                        <input type="password" id="gPassword" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-right-to-bracket"></i> تسجيل الدخول</button>
                </form>
                </section>

            <section class="tab-panel panel-register">
                <form method="post" action="" class="gate-form" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="register">
                    <div class="form-group">
                        <label for="gName"><i class="fa-solid fa-user"></i> الاسم الكامل *</label>
                        <input type="text" id="gName" name="name" class="form-control" value="<?= e($oldName) ?>" placeholder="اسمك الكامل" required>
                    </div>
                    <div class="form-group">
                        <label for="gEmail"><i class="fa-solid fa-envelope"></i> البريد الإلكتروني *</label>
                        <input type="email" id="gEmail" name="email" class="form-control" value="<?= e($oldEmail) ?>" placeholder="name@example.com" dir="ltr" required>
                    </div>
                    <div class="form-group">
                        <label for="gPhone"><i class="fa-solid fa-phone"></i> رقم الهاتف (اختياري)</label>
                        <input type="tel" id="gPhone" name="phone" class="form-control" value="<?= e($oldPhone) ?>" placeholder="+966 5X XXX XXXX" dir="ltr">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="gPass"><i class="fa-solid fa-lock"></i> كلمة المرور *</label>
                            <input type="password" id="gPass" name="password" class="form-control" placeholder="6 أحرف على الأقل" required>
                        </div>
                        <div class="form-group">
                            <label for="gPass2"><i class="fa-solid fa-lock"></i> تأكيد كلمة المرور *</label>
                            <input type="password" id="gPass2" name="password2" class="form-control" placeholder="أعد كتابة كلمة المرور" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-user-plus"></i> إنشاء الحساب</button>
                </form>
                <p class="gate-hint"><i class="fa-solid fa-shield-halved"></i> بعد التسجيل ستدخل مباشرة لمشاهدة تفاصيل المشاريع الكاملة.</p>
            </section>
        </div>

        <a href="<?= e(base_url('index.php')) ?>" class="gate-back"><i class="fa-solid fa-arrow-right"></i> تصفح الموقع كزائر</a>
    </div>

    <p class="gate-footer"><?= e(setting('footer_text', 'جميع الحقوق محفوظة.')) ?></p>
</div>

</body>
</html>