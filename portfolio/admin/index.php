<?php
$page_title = 'لوحة المعلومات';
$nav_active = 'dashboard';
require_once __DIR__ . '/includes/auth.php';

$db = db();

$stats = [
    'projects'  => (int)$db->query('SELECT COUNT(*) FROM projects')->fetchColumn(),
    'services'  => (int)$db->query('SELECT COUNT(*) FROM services')->fetchColumn(),
    'categories'=> (int)$db->query('SELECT COUNT(*) FROM categories')->fetchColumn(),
    'messages'  => (int)$db->query('SELECT COUNT(*) FROM messages')->fetchColumn(),
    'unread'    => (int)$db->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn(),
];
$stmtUsers = $db->prepare('SELECT COUNT(*) FROM users WHERE role = ?');
$stmtUsers->execute(['user']);
$stats['users'] = (int)$stmtUsers->fetchColumn();

$recent_projects = $db->query(
    'SELECT p.id, p.title, p.featured, c.name AS category_name, p.created_at
     FROM projects p JOIN categories c ON c.id = p.category_id
     ORDER BY p.created_at DESC LIMIT 5'
)->fetchAll();

$recent_messages = $db->query('SELECT * FROM messages ORDER BY created_at DESC LIMIT 5')->fetchAll();

$recent_users = $db->query(
    'SELECT id, full_name, email, role, last_login, created_at
     FROM users ORDER BY created_at DESC LIMIT 5'
)->fetchAll();

$popular_cats = $db->query(
    'SELECT c.name, COUNT(p.id) AS cnt
     FROM categories c LEFT JOIN projects p ON p.category_id = c.id
     GROUP BY c.id, c.name ORDER BY cnt DESC LIMIT 4'
)->fetchAll();
$max_cat = max(array_column($popular_cats, 'cnt') ?: [1]);
?>
<?php require __DIR__ . '/includes/header.php'; ?>

<div class="stat-cards">
    <div class="stat-card" onclick="location.href='projects.php'">
        <span class="stat-card-icon blue"><i class="fa-solid fa-diagram-project"></i></span>
        <div><span class="stat-card-num"><?= $stats['projects'] ?></span><span class="stat-card-label">مشروع</span></div>
        <small>إجمالي المشاريع</small>
    </div>
    <div class="stat-card" onclick="location.href='services.php'">
        <span class="stat-card-icon green"><i class="fa-solid fa-briefcase"></i></span>
        <div><span class="stat-card-num"><?= $stats['services'] ?></span><span class="stat-card-label">خدمة</span></div>
        <small>الخدمات المعروضة</small>
    </div>
    <div class="stat-card" onclick="location.href='categories.php'">
        <span class="stat-card-icon purple"><i class="fa-solid fa-folder-tree"></i></span>
        <div><span class="stat-card-num"><?= $stats['categories'] ?></span><span class="stat-card-label">تصنيف</span></div>
        <small>تصنيفات الأعمال</small>
    </div>
    <div class="stat-card" onclick="location.href='messages.php'">
        <span class="stat-card-icon <?= $stats['unread'] > 0 ? 'red' : 'amber' ?>"><i class="fa-solid fa-envelope"></i></span>
        <div>
            <span class="stat-card-num"><?= $stats['unread'] ?></span><span class="stat-card-label">جديد</span>
            <?php if ($stats['unread'] > 0): ?><span class="stat-ping"></span><?php endif; ?>
        </div>
        <small>رسائل غير مقروءة</small>
    </div>
    <div class="stat-card" onclick="location.href='users.php'">
        <span class="stat-card-icon blue"><i class="fa-solid fa-users"></i></span>
        <div><span class="stat-card-num"><?= $stats['users'] ?></span><span class="stat-card-label">مستخدم</span></div>
        <small>زوار مسجّلون</small>
    </div>
</div>

<div class="dash-grid">
    <div class="panel">
        <div class="panel-head">
            <h3><i class="fa-solid fa-bolt"></i> أحدث المشاريع</h3>
            <a href="projects.php" class="panel-more">عرض الكل <i class="fa-solid fa-arrow-left"></i></a>
        </div>
        <div class="table-scroll">
            <table class="table">
                <thead>
                    <tr><th>المشروع</th><th>التصنيف</th><th>الحالة</th></tr>
                </thead>
                <tbody>
                    <?php if (!$recent_projects): ?>
                        <tr><td colspan="3" class="td-empty">لا توجد مشاريع بعد</td></tr>
                    <?php endif; ?>
                    <?php foreach ($recent_projects as $p): ?>
                    <tr>
                        <td><a href="projects_form.php?id=<?= (int)$p['id'] ?>" class="td-link"><?= e($p['title']) ?></a></td>
                        <td><span class="chip"><?= e($p['category_name']) ?></span></td>
                        <td><?= $p['featured'] ? '<span class="badge badge-feat">مميز</span>' : '<span class="badge badge-normal">عادي</span>' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <h3><i class="fa-solid fa-envelope-open-text"></i> أحدث الرسائل</h3>
            <a href="messages.php" class="panel-more">عرض الكل <i class="fa-solid fa-arrow-left"></i></a>
        </div>
        <div class="msg-list">
            <?php if (!$recent_messages): ?>
                <p class="td-empty">لا توجد رسائل بعد</p>
            <?php endif; ?>
            <?php foreach ($recent_messages as $m): ?>
                <a href="messages.php?id=<?= (int)$m['id'] ?>" class="msg-item <?= !$m['is_read'] ? 'unread' : '' ?>">
                    <span class="msg-avatar"><i class="fa-solid fa-user"></i></span>
                    <div class="msg-body">
                        <strong><?= e($m['name']) ?></strong>
                        <p><?= e(mb_substr($m['message'], 0, 46)) ?>…</p>
                    </div>
                    <?php if (!$m['is_read']): ?><span class="msg-dot"></span><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="panel">
        <div class="panel-head">
            <h3><i class="fa-solid fa-users"></i> أحدث الحسابات</h3>
            <a href="users.php" class="panel-more">عرض الكل <i class="fa-solid fa-arrow-left"></i></a>
        </div>
        <div class="table-scroll">
            <table class="table">
                <thead>
                    <tr><th>الاسم</th><th>البريد الإلكتروني</th><th>الدور</th><th>التسجيل</th></tr>
                </thead>
                <tbody>
                    <?php if (!$recent_users): ?>
                        <tr><td colspan="4" class="td-empty">لا توجد حسابات بعد</td></tr>
                    <?php endif; ?>
                    <?php foreach ($recent_users as $u): ?>
                    <tr>
                        <td><span class="td-strong"><?= e($u['full_name'] ?: $u['username']) ?></span></td>
                        <td dir="ltr" class="muted"><?= e($u['email']) ?></td>
                        <td><?= $u['role'] === 'admin' ? '<span class="badge badge-feat">مدير</span>' : '<span class="badge badge-normal">زائر</span>' ?></td>
                        <td class="muted"><?= e(date('Y-m-d', strtotime($u['created_at']))) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<div class="panel">
    <div class="panel-head"><h3><i class="fa-solid fa-chart-pie"></i> توزيع المشاريع حسب التصنيف</h3></div>
    <div class="cat-bars">
        <?php foreach ($popular_cats as $c): ?>
            <div class="cat-bar-row">
                <span class="cat-bar-name"><?= e($c['name']) ?></span>
                <div class="cat-bar-track"><div class="cat-bar-fill" style="width: <?= round(($c['cnt'] / $max_cat) * 100) ?>%"></div></div>
                <span class="cat-bar-num"><?= (int)$c['cnt'] ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>