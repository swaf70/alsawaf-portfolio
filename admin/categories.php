<?php
$page_title = 'إدارة التصنيفات';
$nav_active = 'categories';
require_once __DIR__ . '/includes/auth.php';

$db = db();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        flash('error', 'انتهت صلاحية الجلسة، أعد المحاولة.');
        redirect('categories.php');
    }

    if ($action === 'create' || $action === 'update') {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $icon = trim($_POST['icon'] ?? 'fa-folder');
        $id   = (int)($_POST['id'] ?? 0);

        if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
            $errors[] = 'اسم التصنيف يجب أن يكون بين حرفين و 100 حرف.';
        }
        $slug = $slug !== '' ? $slug : $name;
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9\-]+/', '-', $slug), '-'));

        if ($slug === '' && $action === 'create') {
            $errors[] = 'الرابط الاختصاري غير صالح.';
        }

        if (!$errors) {
            try {
                if ($action === 'create') {
                    $check = $db->prepare('SELECT id FROM categories WHERE slug = ?');
                    $check->execute([$slug]);
                    if ($check->fetch()) {
                        $slug .= '-' . time();
                    }
                    $db->prepare('INSERT INTO categories (name, slug, icon) VALUES (?, ?, ?)')->execute([$name, $slug, $icon]);
                    flash('success', 'تمت إضافة التصنيف بنجاح.');
                } else {
                    $db->prepare('UPDATE categories SET name = ?, slug = ?, icon = ? WHERE id = ?')->execute([$name, $slug, $icon, $id]);
                    flash('success', 'تم تحديث التصنيف بنجاح.');
                }
            } catch (Throwable $e) {
                $errors[] = 'تعذر الحفظ: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        try {
            $db->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
            flash('success', 'تم حذف التصنيف بنجاح.');
        } catch (Throwable $e) {
            flash('error', 'لا يمكن حذف هذا التصنيف: يحتوي على مشاريع مرتبطة.');
        }
        redirect('categories.php');
    }

    if (!$errors) {
        redirect('categories.php');
    }
}

$edit_id  = (int)($_GET['edit'] ?? 0);
$editing  = null;
if ($edit_id) {
    $stmt = $db->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([$edit_id]);
    $editing = $stmt->fetch();
}

$cats = $db->query(
    'SELECT c.*, (SELECT COUNT(*) FROM projects p WHERE p.category_id = c.id) AS project_count
     FROM categories c ORDER BY c.id'
)->fetchAll();

$icons = ['fa-folder', 'fa-globe', 'fa-mobile-screen', 'fa-network-wired', 'fa-shield-halved', 'fa-lock', 'fa-code', 'fa-server', 'fa-cloud', 'fa-database', 'fa-bug', 'fa-wifi'];
?>
<?php require __DIR__ . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <h2 class="page-title">التصنيفات</h2>
        <p class="page-sub">تنظيم المشاريع والخدمات ضمن تصنيفات واضحة</p>
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

<div class="cat-layout">
    <div class="panel">
        <div class="panel-head">
            <h3><i class="fa-solid fa-table-list"></i> التصنيفات الحالية</h3>
            <span class="count-chip"><?= count($cats) ?></span>
        </div>
        <div class="table-scroll">
            <table class="table">
                <thead>
                    <tr><th>التصنيف</th><th>المشاريع</th><th>عمليات</th></tr>
                </thead>
                <tbody>
                    <?php if (!$cats): ?>
                        <tr><td colspan="3" class="td-empty">لا توجد تصنيفات بعد</td></tr>
                    <?php endif; ?>
                    <?php foreach ($cats as $c): ?>
                    <tr>
                        <td>
                            <div class="td-service">
                                <span class="svc-icon"><i class="fa-solid <?= e($c['icon']) ?>"></i></span>
                                <div>
                                    <span class="td-strong"><?= e($c['name']) ?></span>
                                    <small class="muted" dir="ltr">/<?= e($c['slug']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td><span class="chip"><?= (int)$c['project_count'] ?> مشروع</span></td>
                        <td class="td-actions">
                            <a href="categories.php?edit=<?= (int)$c['id'] ?>" class="btn-icon-sm edit" title="تعديل"><i class="fa-solid fa-pen"></i></a>
                            <form method="post" action="categories.php" class="inline-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                <button type="button" class="btn-icon-sm del confirm-submit" data-confirm-text="سيتم حذف هذا التصنيف. تأكد أنه لا يحتوي على مشاريع مرتبطة." title="حذف"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel">
        <?php if ($editing): ?>
            <div class="panel-head"><h3><i class="fa-solid fa-pen"></i> تعديل التصنيف</h3></div>
        <?php else: ?>
            <div class="panel-head"><h3><i class="fa-solid fa-plus"></i> إضافة تصنيف جديد</h3></div>
        <?php endif; ?>

        <form method="post" action="categories.php" class="admin-form">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="<?= $editing ? 'update' : 'create' ?>">
            <input type="hidden" name="id" value="<?= $editing ? (int)$editing['id'] : 0 ?>">

            <div class="form-group">
                <label for="cname">اسم التصنيف *</label>
                <input type="text" id="cname" name="name" class="form-control" value="<?= e($_POST['name'] ?? $editing['name'] ?? '') ?>" placeholder="مثال: أدوات شبكات" required>
            </div>
            <div class="form-group">
                <label for="cslug">الرابط الاختصاري (اختياري — يُولّد تلقائياً)</label>
                <input type="text" id="cslug" name="slug" class="form-control" value="<?= e($_POST['slug'] ?? $editing['slug'] ?? '') ?>" placeholder="network-tools" dir="ltr">
            </div>
            <div class="form-group">
                <label>الأيقونة</label>
                <div class="icon-picker">
                    <?php foreach ($icons as $ic): ?>
                        <label class="icon-option">
                            <input type="radio" name="icon" value="<?= e($ic) ?>" <?= ($_POST['icon'] ?? $editing['icon'] ?? 'fa-folder') === $ic ? 'checked' : '' ?>>
                            <i class="fa-solid <?= e($ic) ?>"></i>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> <?= $editing ? 'حفظ التعديلات' : 'إضافة التصنيف' ?></button>
                <?php if ($editing): ?><a href="categories.php" class="btn btn-ghost">إلغاء التعديل</a><?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>