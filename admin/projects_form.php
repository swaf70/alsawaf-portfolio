<?php
$page_title = 'إدارة المشاريع';
$nav_active = 'projects';
require_once __DIR__ . '/includes/auth.php';

$db  = db();
$id  = (int)($_GET['id'] ?? 0);
$project = null;

if ($id) {
    $stmt = $db->prepare('SELECT * FROM projects WHERE id = ?');
    $stmt->execute([$id]);
    $project = $stmt->fetch();
    if (!$project) {
        flash('error', 'المشروع المطلوب غير موجود.');
        redirect('projects.php');
    }
    $page_title = 'تعديل المشروع: ' . $project['title'];
}

$categories = $db->query('SELECT * FROM categories ORDER BY name')->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'انتهت صلاحية الجلسة، أعد المحاولة.';
    } else {
        $title       = trim($_POST['title'] ?? '');
        $category_id = (int)($_POST['category_id'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $link        = trim($_POST['link'] ?? '');
        $featured    = isset($_POST['featured']) ? 1 : 0;
        $remove_img  = isset($_POST['remove_image']) ? 1 : 0;

        if (mb_strlen($title) < 3 || mb_strlen($title) > 150) {
            $errors[] = 'عنوان المشروع يجب أن يكون بين 3 و 150 حرفاً.';
        }
        if ($category_id <= 0 || !category_name($category_id)) {
            $errors[] = 'يرجى اختيار تصنيف صحيح.';
        }
        if (mb_strlen($description) < 10) {
            $errors[] = 'الوصف يجب أن يكون 10 أحرف على الأقل.';
        }
        if ($link !== '' && !filter_var($link, FILTER_VALIDATE_URL)) {
            $errors[] = 'رابط المشروع غير صالح.';
        }

        $new_image = null;
        if (!empty($_FILES['image']['name'])) {
            try {
                $new_image = upload_image($_FILES['image'], '');
            } catch (RuntimeException $ex) {
                $errors[] = $ex->getMessage() === 'no_file' ? '' : $ex->getMessage();
            }
        }

        if (empty($errors)) {
            if ($project) {
                if ($remove_img && $project['image']) {
                    delete_upload($project['image']);
                    $new_image = $new_image ?? '';
                } elseif ($new_image && $project['image']) {
                    delete_upload($project['image']);
                }
                $target_image = $new_image ?: ($remove_img ? '' : $project['image']);
                $stmt = $db->prepare(
                    'UPDATE projects SET title = ?, category_id = ?, description = ?, image = ?, link = ?, featured = ? WHERE id = ?'
                );
                $stmt->execute([$title, $category_id, $description, $target_image, $link, $featured, $id]);
                flash('success', 'تم تحديث المشروع بنجاح.');
            } else {
                $stmt = $db->prepare(
                    'INSERT INTO projects (title, category_id, description, image, link, featured) VALUES (?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute([$title, $category_id, $description, $new_image, $link, $featured]);
                flash('success', 'تمت إضافة المشروع بنجاح.');
            }
            redirect('projects.php');
        }
    }
}

$form = $project ?? ['title' => '', 'category_id' => $categories[0]['id'] ?? 0, 'description' => '', 'link' => '', 'featured' => 0];
?>
<?php require __DIR__ . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <h2 class="page-title"><?= $project ? 'تعديل المشروع' : 'إضافة مشروع جديد' ?></h2>
        <p class="page-sub"><?= $project ? 'قم بتحديث تفاصيل المشروع' : 'أدخل تفاصيل المشروع وأرفق صورة الغلاف' ?></p>
    </div>
    <a href="projects.php" class="btn btn-ghost"><i class="fa-solid fa-arrow-right"></i> عودة للقائمة</a>
</div>

<?php if ($errors): ?>
    <div class="alert-box">
        <ul>
            <?php foreach ($errors as $er): if ($er === '') continue; ?>
                <li><i class="fa-solid fa-circle-exclamation"></i> <?= e($er) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="panel form-panel">
    <form method="post" action="" enctype="multipart/form-data" class="admin-form">
        <?= csrf_field() ?>
        <div class="form-grid">
            <div class="form-group">
                <label for="title">عنوان المشروع *</label>
                <input type="text" id="title" name="title" class="form-control" value="<?= e($_POST['title'] ?? $form['title']) ?>" placeholder="مثال: منصة تعليم إلكتروني ذكية" required>
            </div>
            <div class="form-group">
                <label for="category_id">التصنيف *</label>
                <select id="category_id" name="category_id" class="form-select" required>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= (int)($_POST['category_id'] ?? $form['category_id']) === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="description">وصف المشروع *</label>
            <textarea id="description" name="description" rows="6" class="form-control" required><?= e($_POST['description'] ?? $form['description']) ?></textarea>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="link">رابط المشروع (اختياري)</label>
                <input type="url" id="link" name="link" class="form-control" value="<?= e($_POST['link'] ?? $form['link'] ?? '') ?>" placeholder="https://example.com" dir="ltr">
            </div>
            <div class="form-group">
                <label>صورة الغلاف</label>
                <?php if ($project && $project['image']): ?>
                    <div class="img-current">
                        <img src="<?= e(base_url($project['image'])) ?>" alt="">
                        <label class="img-remove-check">
                            <input type="checkbox" name="remove_image" value="1"> حذف الصورة الحالية
                        </label>
                    </div>
                <?php endif; ?>
                <input type="file" name="image" class="form-control form-file" accept="image/jpeg,image/png,image/webp,image/gif">
                <small class="form-hint"><i class="fa-solid fa-circle-info"></i> الصيغ المسموحة: JPG, PNG, WebP, GIF — بحد أقصى 2MB</small>
            </div>
        </div>

        <label class="checkbox-line">
            <input type="checkbox" name="featured" value="1" <?= ($_POST['featured'] ?? $form['featured']) ? 'checked' : '' ?>>
            <span><i class="fa-solid fa-star"></i> عرض المشروع في قسم المميز (أولاً)</span>
        </label>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> <?= $project ? 'حفظ التعديلات' : 'إضافة المشروع' ?></button>
            <a href="projects.php" class="btn btn-ghost">إلغاء</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>