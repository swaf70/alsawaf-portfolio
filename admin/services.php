<?php
$page_title = 'إدارة الخدمات';
$nav_active = 'services';
require_once __DIR__ . '/includes/auth.php';

$db = db();

if (($_POST['toggle_featured'] ?? 0)) {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        flash('error', 'انتهت صلاحية الجلسة.');
    } else {
        $st = $db->prepare('UPDATE services SET featured = 1 - featured WHERE id = ?');
        $st->execute([(int)$_POST['toggle_featured']]);
        flash('success', 'تم تحديث حالة التمييز.');
    }
    redirect('services.php');
}

$search = trim($_GET['search'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$per_page = 8;

$where  = [];
$params = [];
if ($search !== '') {
    $where[] = 'title LIKE ?';
    $params[] = '%' . $search . '%';
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $db->prepare("SELECT COUNT(*) FROM services $where_sql");
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();

$pages  = max(1, (int)ceil($total / $per_page));
$page   = min($page, $pages);
$offset = ($page - 1) * $per_page;

$stmt = $db->prepare("SELECT * FROM services $where_sql ORDER BY created_at DESC LIMIT $per_page OFFSET $offset");
$stmt->execute($params);
$services = $stmt->fetchAll();

function service_page_url(int $i, string $s): string {
    $u = 'services.php?page=' . $i;
    if ($s !== '') $u .= '&search=' . urlencode($s);
    return $u;
}
?>
<?php require __DIR__ . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <h2 class="page-title">الخدمات</h2>
        <p class="page-sub">إدارة الخدمات والمنتجات الرقمية المعروضة</p>
    </div>
    <a href="services_form.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> إضافة خدمة جديدة</a>
</div>

<div class="panel">
    <form method="get" action="" class="filter-bar">
        <div class="search-input">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="search" value="<?= e($search) ?>" placeholder="ابحث باسم الخدمة...">
        </div>
        <button type="submit" class="btn btn-ghost"><i class="fa-solid fa-filter"></i> تصفية</button>
        <?php if ($search !== ''): ?>
            <a href="services.php" class="btn btn-ghost"><i class="fa-solid fa-rotate"></i> إلغاء</a>
        <?php endif; ?>
    </form>
</div>

<div class="panel">
    <div class="table-scroll">
        <table class="table">
            <thead>
                <tr><th>الخدمة</th><th>السعر</th><th>التوفر</th><th>مميز</th><th>عمليات</th></tr>
            </thead>
            <tbody>
                <?php if (!$services): ?>
                    <tr><td colspan="5" class="td-empty"><i class="fa-solid fa-folder-open"></i> لا توجد خدمات — أضف أول خدمة الآن</td></tr>
                <?php endif; ?>
                <?php foreach ($services as $s): ?>
                <tr>
                    <td>
                        <div class="td-service">
                            <span class="svc-icon"><i class="fa-solid <?= e($s['icon']) ?>"></i></span>
                            <div>
                                <span class="td-strong"><?= e($s['title']) ?></span>
                                <small class="muted"><?= e(mb_substr($s['description'] ?? '', 0, 55)) ?>…</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php if ($s['price'] !== null): ?>
                            <span class="price-text">$<?= number_format((float)$s['price'], 0) ?></span>
                            <?php if ($s['price_note']): ?><small class="muted block"><?= e($s['price_note']) ?></small><?php endif; ?>
                        <?php else: ?>
                            <span class="muted">عند الطلب</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                            $avail = ['available' => ['متوفر', 'badge-available'], 'limited' => ['كمية محدودة', 'badge-limited'], 'unavailable' => ['غير متاح', 'badge-unavailable']][$s['availability']];
                        ?>
                        <span class="badge <?= $avail[1] ?>"><?= e($avail[0]) ?></span>
                    </td>
                    <td>
                        <form method="post" action="services.php" class="inline-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="toggle_featured" value="<?= (int)$s['id'] ?>">
                            <button type="submit" class="btn-stars <?= $s['featured'] ? 'star-on' : '' ?>" title="<?= $s['featured'] ? 'إزالة التمييز' : 'تعيين كمميز' ?>"><i class="fa-solid fa-star"></i></button>
                        </form>
                    </td>
                    <td class="td-actions">
                        <a href="services_form.php?id=<?= (int)$s['id'] ?>" class="btn-icon-sm edit" title="تعديل"><i class="fa-solid fa-pen"></i></a>
                        <button class="btn-icon-sm del" data-confirm-action="services_delete.php?id=<?= (int)$s['id'] ?>" data-confirm-title="حذف الخدمة" data-confirm-text="سيتم حذف الخدمة نهائياً. هل أنت متأكد؟" title="حذف"><i class="fa-solid fa-trash"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $pages; $i++): ?>
                <a href="<?= e(service_page_url($i, $search)) ?>" class="page-link <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>