<?php
$page_title = 'إدارة المشاريع';
$nav_active = 'projects';
require_once __DIR__ . '/includes/auth.php';

$db = db();

if (($_POST['toggle_featured'] ?? 0)) {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        flash('error', 'انتهت صلاحية الجلسة.');
    } else {
        $stmt = $db->prepare('UPDATE projects SET featured = 1 - featured WHERE id = ?');
        $stmt->execute([(int)$_POST['toggle_featured']]);
        flash('success', 'تم تحديث حالة التمييز.');
    }
    redirect('projects.php');
}

$search = trim($_GET['search'] ?? '');
$cat_id = (int)($_GET['category'] ?? 0);
$page   = max(1, (int)($_GET['page'] ?? 1));
$per_page = 8;

$where  = [];
$params = [];
if ($search !== '') {
    $where[] = 'p.title LIKE ?';
    $params[] = '%' . $search . '%';
}
if ($cat_id > 0) {
    $where[] = 'p.category_id = ?';
    $params[] = $cat_id;
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$count_sql = "SELECT COUNT(*) FROM projects p $where_sql";
$stmt = $db->prepare($count_sql);
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();

$pages  = max(1, (int)ceil($total / $per_page));
$page   = min($page, $pages);
$offset = ($page - 1) * $per_page;

$list_sql = "SELECT p.*, c.name AS category_name
             FROM projects p JOIN categories c ON c.id = p.category_id
             $where_sql
             ORDER BY p.created_at DESC
             LIMIT $per_page OFFSET $offset";
$stmt = $db->prepare($list_sql);
$stmt->execute($params);
$projects = $stmt->fetchAll();

$categories = $db->query('SELECT * FROM categories ORDER BY name')->fetchAll();

function project_list_url(int $i, string $s, int $c): string {
    $u = 'projects.php?page=' . $i;
    if ($s !== '') $u .= '&search=' . urlencode($s);
    if ($c > 0)    $u .= '&category=' . $c;
    return $u;
}
?>
<?php require __DIR__ . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <h2 class="page-title">المشاريع</h2>
        <p class="page-sub">إدارة كاملة للمشاريع: إضافة، تعديل، حذف، بحث وفلترة</p>
    </div>
    <a href="projects_form.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> إضافة مشروع جديد</a>
</div>

<div class="panel">
    <form method="get" action="" class="filter-bar">
        <div class="search-input">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="search" value="<?= e($search) ?>" placeholder="ابحث باسم المشروع...">
        </div>
        <select name="category" class="form-select">
            <option value="0">كل التصنيفات</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= (int)$c['id'] ?>" <?= $cat_id === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-ghost"><i class="fa-solid fa-filter"></i> تصفية</button>
        <?php if ($search !== '' || $cat_id > 0): ?>
            <a href="projects.php" class="btn btn-ghost"><i class="fa-solid fa-rotate"></i> إلغاء</a>
        <?php endif; ?>
    </form>
</div>

<div class="panel">
    <div class="table-scroll">
        <table class="table">
            <thead>
                <tr>
                    <th>الصورة</th><th>العنوان</th><th>التصنيف</th><th>مميز</th><th>الرابط</th><th>عمليات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$projects): ?>
                    <tr><td colspan="6" class="td-empty"><i class="fa-solid fa-folder-open"></i> لا توجد مشاريع — أضف أول مشروع الآن</td></tr>
                <?php endif; ?>
                <?php foreach ($projects as $p): ?>
                <tr>
                    <td>
                        <?php if ($p['image']): ?>
                            <img src="<?= e(base_url($p['image'])) ?>" alt="" class="td-thumb">
                        <?php else: ?>
                            <span class="td-thumb td-thumb-none"><i class="fa-solid fa-image"></i></span>
                        <?php endif; ?>
                    </td>
                    <td class="td-strong"><?= e($p['title']) ?></td>
                    <td><span class="chip"><?= e($p['category_name']) ?></span></td>
                    <td>
                        <form method="post" action="projects.php" class="inline-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="toggle_featured" value="<?= (int)$p['id'] ?>">
                            <button type="submit" class="btn-stars <?= $p['featured'] ? 'star-on' : '' ?>" title="<?= $p['featured'] ? 'إزالة من المميز' : 'تعيين كمميز' ?>">
                                <i class="fa-solid fa-star"></i>
                            </button>
                        </form>
                    </td>
                    <td>
                        <?php if ($p['link']): ?>
                            <a href="<?= e($p['link']) ?>" target="_blank" rel="noopener" class="btn-icon-sm" title="زيارة"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                        <?php else: ?>
                            <span class="muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="td-actions">
                        <a href="projects_form.php?id=<?= (int)$p['id'] ?>" class="btn-icon-sm edit" title="تعديل"><i class="fa-solid fa-pen"></i></a>
                        <button class="btn-icon-sm del" data-confirm-action="projects_delete.php?id=<?= (int)$p['id'] ?>" data-confirm-title="حذف المشروع" data-confirm-text="سيتم حذف المشروع وصورته نهائياً. هل أنت متأكد؟" title="حذف"><i class="fa-solid fa-trash"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $pages; $i++): ?>
                <a href="<?= e(project_list_url($i, $search, $cat_id)) ?>" class="page-link <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>