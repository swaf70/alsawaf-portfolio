<?php
$page_title = 'المستخدمون';
$nav_active = 'users';
require_once __DIR__ . '/includes/auth.php';

$db = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        flash('error', 'انتهت صلاحية الجلسة، أعد المحاولة.');
        redirect('users.php');
    }
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0 && $id !== (int)$_SESSION['user_id']) {
        try {
            $stmt = $db->prepare('DELETE FROM users WHERE id = ? AND role != ?');
            $stmt->execute([$id, 'admin']);
            flash('success', $stmt->rowCount() ? 'تم حذف المستخدم بنجاح.' : 'لا يمكن حذف هذا الحساب.');
        } catch (Throwable $e) {
            flash('error', 'تعذر حذف المستخدم: ' . $e->getMessage());
        }
    } else {
        flash('error', 'لا يمكن حذف الحساب الحالي.');
    }
    redirect('users.php');
}

$users = $db->query(
    'SELECT id, username, full_name, email, phone, role, last_login, created_at
     FROM users ORDER BY role DESC, created_at DESC'
)->fetchAll();

$guest_count = 0;
foreach ($users as $u) {
    if ($u['role'] !== 'admin') {
        $guest_count++;
    }
}
?>
<?php require __DIR__ . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <h2 class="page-title">المستخدمون</h2>
        <p class="page-sub">حسابات الزوار المسجّلين الذين يطّلعون على تفاصيل مشاريعك</p>
    </div>
    <span class="count-chip"><?= (int)$guest_count ?> زائر مسجّل</span>
</div>

<div class="panel">
    <div class="panel-head">
        <h3><i class="fa-solid fa-users"></i> قائمة الحسابات</h3>
        <span class="count-chip"><?= count($users) ?></span>
    </div>
    <div class="table-scroll">
        <table class="table">
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>البريد الإلكتروني</th>
                    <th>الهاتف</th>
                    <th>الدور</th>
                    <th>آخر دخول</th>
                    <th>تاريخ التسجيل</th>
                    <th>عمليات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$users): ?>
                    <tr><td colspan="7" class="td-empty">لا توجد حسابات بعد</td></tr>
                <?php endif; ?>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td>
                        <div class="td-service">
                            <span class="svc-icon"><i class="fa-solid fa-user"></i></span>
                            <div>
                                <span class="td-strong"><?= e($u['full_name'] ?: $u['username']) ?></span>
                                <small class="muted"><?= e($u['username']) ?></small>
                            </div>
                        </div>
                    </td>
                    <td dir="ltr" class="muted"><?= e($u['email']) ?></td>
                    <td dir="ltr" class="muted"><?= e($u['phone'] ?: '—') ?></td>
                    <td>
                        <?php if ($u['role'] === 'admin'): ?>
                            <span class="badge badge-feat"><i class="fa-solid fa-shield-halved"></i> مدير</span>
                        <?php else: ?>
                            <span class="badge badge-normal">زائر</span>
                        <?php endif; ?>
                    </td>
                    <td class="muted"><?= $u['last_login'] ? e(date('Y-m-d H:i', strtotime($u['last_login']))) : 'لم يدخل بعد' ?></td>
                    <td class="muted"><?= e(date('Y-m-d', strtotime($u['created_at']))) ?></td>
                    <td class="td-actions">
                        <?php if ($u['role'] !== 'admin'): ?>
                            <form method="post" action="users.php" class="inline-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                <button type="button" class="btn-icon-sm del confirm-submit" data-confirm-text="سيتم حذف حساب هذا المستخدم نهائياً." title="حذف"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        <?php else: ?>
                            <span class="muted">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>