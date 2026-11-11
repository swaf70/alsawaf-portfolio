<?php
$page_title = 'تغيير كلمة المرور';
$nav_active = 'password';
require_once __DIR__ . '/includes/auth.php';

$db = db();
$errors = [];
$ok = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'انتهت صلاحية الجلسة، أعد المحاولة.';
    } else {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $stmt = $db->prepare('SELECT password FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $hash = $stmt->fetchColumn();

        if (!password_verify($current, (string)$hash)) {
            $errors[] = 'كلمة المرور الحالية غير صحيحة.';
        }
        if (mb_strlen($new) < 6) {
            $errors[] = 'كلمة المرور الجديدة يجب أن تكون 6 أحرف على الأقل.';
        }
        if ($new !== $confirm) {
            $errors[] = 'تأكيد كلمة المرور غير مطابق.';
        }

        if (empty($errors)) {
            $db->prepare('UPDATE users SET password = ? WHERE id = ?')
               ->execute([password_hash($new, PASSWORD_BCRYPT), $_SESSION['user_id']]);
            flash('success', 'تم تغيير كلمة المرور بنجاح.');
            redirect('change_password.php');
        }
    }
}
?>
<?php require __DIR__ . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <h2 class="page-title">تغيير كلمة المرور</h2>
        <p class="page-sub">تحديث كلمة مرور حساب المدير</p>
    </div>
</div>

<?php if ($errors): ?>
    <div class="alert-box">
        <ul>
            <?php foreach ($errors as $er): ?>
                <li><i class="fa-solid fa-circle-exclamation"></i> <?= e($er) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="panel form-panel narrow-panel">
    <form method="post" action="" class="admin-form">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="current_password">كلمة المرور الحالية *</label>
            <div class="password-wrap">
                <input type="password" id="current_password" name="current_password" class="form-control" required autocomplete="current-password">
                <button type="button" class="password-toggle" data-toggle="current_password"><i class="fa-solid fa-eye"></i></button>
            </div>
        </div>
        <div class="form-group">
            <label for="new_password">كلمة المرور الجديدة *</label>
            <div class="password-wrap">
                <input type="password" id="new_password" name="new_password" class="form-control" required autocomplete="new-password">
                <button type="button" class="password-toggle" data-toggle="new_password"><i class="fa-solid fa-eye"></i></button>
            </div>
        </div>
        <div class="form-group">
            <label for="confirm_password">تأكيد كلمة المرور الجديدة *</label>
            <div class="password-wrap">
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required autocomplete="new-password">
                <button type="button" class="password-toggle" data-toggle="confirm_password"><i class="fa-solid fa-eye"></i></button>
            </div>
        </div>
        <div class="form-hint-box">
            <i class="fa-solid fa-shield-halved"></i> تأكد من استخدام كلمة مرور قوية تحتوي على أحرف وأرقام ورموز.
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-key"></i> تحديث كلمة المرور</button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>