<?php
$page_title = 'الرسائل الواردة';
$nav_active = 'messages';
require_once __DIR__ . '/includes/auth.php';

$db = db();

$view_id = (int)($_GET['id'] ?? 0);
if ($view_id) {
    $db->prepare('UPDATE messages SET is_read = 1 WHERE id = ?')->execute([$view_id]);
    $stmt = $db->prepare('SELECT * FROM messages WHERE id = ?');
    $stmt->execute([$view_id]);
    $viewing = $stmt->fetch();
    if (!$viewing) {
        flash('error', 'الرسالة غير موجودة.');
        redirect('messages.php');
    }
}

if (($_POST['delete'] ?? 0)) {
    if (verify_csrf($_POST['csrf_token'] ?? null)) {
        $db->prepare('DELETE FROM messages WHERE id = ?')->execute([(int)$_POST['delete']]);
        flash('success', 'تم حذف الرسالة.');
    }
    redirect('messages.php');
}

$search = trim($_GET['search'] ?? '');
$filter = $_GET['filter'] ?? 'all';
$page   = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;

$where  = [];
$params = [];
if ($search !== '') {
    $where[] = '(name LIKE ? OR email LIKE ? OR subject LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}
if ($filter === 'unread') {
    $where[] = 'is_read = 0';
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $db->prepare("SELECT COUNT(*) FROM messages $where_sql");
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();

$pages  = max(1, (int)ceil($total / $per_page));
$page   = min($page, $pages);
$offset = ($page - 1) * $per_page;

$stmt = $db->prepare("SELECT * FROM messages $where_sql ORDER BY created_at DESC LIMIT $per_page OFFSET $offset");
$stmt->execute($params);
$messages = $stmt->fetchAll();
?>
<?php require __DIR__ . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <h2 class="page-title">الرسائل الواردة</h2>
        <p class="page-sub">رسائل نموذج التواصل من زوار الموقع</p>
    </div>
</div>

<?php if (isset($viewing)): ?>
    <div class="panel">
        <div class="panel-head">
            <div class="msg-sender">
                <span class="msg-avatar lg"><i class="fa-solid fa-user"></i></span>
                <div>
                    <strong><?= e($viewing['name']) ?></strong>
                    <small class="muted" dir="ltr"><?= e($viewing['email']) ?></small>
                </div>
            </div>
            <a href="messages.php" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-right"></i> رجوع</a>
        </div>
        <div class="msg-view">
            <div class="msg-view-head">
                <span class="chip"><?= e($viewing['subject']) ?></span>
                <small class="muted"><?= e(date('Y-m-d H:i', strtotime($viewing['created_at']))) ?></small>
            </div>
            <p class="msg-text"><?= nl2br(e($viewing['message'])) ?></p>
            <div class="form-actions">
                <a href="mailto:<?= e($viewing['email']) ?>?subject=<?= e(rawurlencode('رد: ' . $viewing['subject'])) ?>" class="btn btn-primary"><i class="fa-solid fa-reply"></i> الرد عبر البريد</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="panel">
    <form method="get" action="" class="filter-bar">
        <div class="search-input">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="search" value="<?= e($search) ?>" placeholder="ابحث في الرسائل...">
        </div>
        <select name="filter" class="form-select">
            <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>كل الرسائل</option>
            <option value="unread" <?= $filter === 'unread' ? 'selected' : '' ?>>غير المقروءة</option>
        </select>
        <button type="submit" class="btn btn-ghost"><i class="fa-solid fa-filter"></i> تصفية</button>
        <?php if ($search !== '' || $filter !== 'all'): ?>
            <a href="messages.php" class="btn btn-ghost"><i class="fa-solid fa-rotate"></i> إلغاء</a>
        <?php endif; ?>
    </form>
</div>

<div class="panel">
    <div class="table-scroll">
        <table class="table">
            <thead>
                <tr><th>المرسل</th><th>الموضوع</th><th>التاريخ</th><th>الحالة</th><th>عمليات</th></tr>
            </thead>
            <tbody>
                <?php if (!$messages): ?>
                    <tr><td colspan="5" class="td-empty"><i class="fa-solid fa-inbox"></i> لا توجد رسائل</td></tr>
                <?php endif; ?>
                <?php foreach ($messages as $m): ?>
                <tr class="<?= !$m['is_read'] ? 'row-unread' : '' ?>">
                    <td>
                        <div class="td-service">
                            <span class="svc-icon <?= !$m['is_read'] ? 'unread-icon' : '' ?>"><i class="fa-solid fa-user"></i></span>
                            <div>
                                <span class="td-strong"><?= e($m['name']) ?></span>
                                <small class="muted block" dir="ltr"><?= e($m['email']) ?></small>
                            </div>
                        </div>
                    </td>
                    <td class="td-subject"><?= e(mb_substr($m['subject'] ?? '', 0, 50)) ?></td>
                    <td class="muted nowrap"><i class="fa-regular fa-clock"></i> <?= e(date('Y-m-d', strtotime($m['created_at']))) ?></td>
                    <td><?= $m['is_read'] ? '<span class="badge badge-normal">مقروءة</span>' : '<span class="badge badge-new">جديدة</span>' ?></td>
                    <td class="td-actions">
                        <a href="messages.php?id=<?= (int)$m['id'] ?>" class="btn-icon-sm" title="عرض"><i class="fa-solid fa-eye"></i></a>
                        <form method="post" action="messages.php" class="inline-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="delete" value="<?= (int)$m['id'] ?>">
                            <button type="button" class="btn-icon-sm del confirm-submit" data-confirm-text="سيتم حذف هذه الرسالة نهائياً. هل أنت متأكد؟" title="حذف"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $pages; $i++): ?>
                <?php $u = 'messages.php?page=' . $i; if ($search !== '') $u .= '&search=' . urlencode($search); if ($filter === 'unread') $u .= '&filter=unread'; ?>
                <a href="<?= e($u) ?>" class="page-link <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>