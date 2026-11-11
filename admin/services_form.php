<?php
$page_title = 'إدارة الخدمات';
$nav_active = 'services';
require_once __DIR__ . '/includes/auth.php';

$db  = db();
$id  = (int)($_GET['id'] ?? 0);
$service = null;

if ($id) {
    $stmt = $db->prepare('SELECT * FROM services WHERE id = ?');
    $stmt->execute([$id]);
    $service = $stmt->fetch();
    if (!$service) {
        flash('error', 'الخدمة المطلوبة غير موجودة.');
        redirect('services.php');
    }
    $page_title = 'تعديل الخدمة: ' . $service['title'];
}

$categories = $db->query('SELECT * FROM categories ORDER BY name')->fetchAll();

$icons = [
    'fa-globe', 'fa-cart-shopping', 'fa-network-wired', 'fa-shield-halved',
    'fa-magnifying-glass-chart', 'fa-server', 'fa-mobile-screen', 'fa-headset',
    'fa-comments', 'fa-lock', 'fa-bug', 'fa-database', 'fa-code', 'fa-cloud',
    'fa-infinity', 'fa-wifi', 'fa-key', 'fa-hdd', 'fa-microchip', 'fa-file-shield',
];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'انتهت صلاحية الجلسة، أعد المحاولة.';
    } else {
        $title       = trim($_POST['title'] ?? '');
        $category_id = (int)($_POST['category_id'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $price       = $_POST['price'] ?? '';
        $price_note  = trim($_POST['price_note'] ?? '');
        $icon        = trim($_POST['icon'] ?? 'fa-briefcase');
        $availability = $_POST['availability'] ?? 'available';
        $featured    = isset($_POST['featured']) ? 1 : 0;

        if (mb_strlen($title) < 3 || mb_strlen($title) > 150) {
            $errors[] = 'اسم الخدمة يجب أن يكون بين 3 و 150 حرفاً.';
        }
        if ($category_id > 0 && !category_name($category_id)) {
            $errors[] = 'التصنيف المختار غير صالح.';
        }
        if (mb_strlen($description) < 10) {
            $errors[] = 'وصف الخدمة يجب أن يكون 10 أحرف على الأقل.';
        }
        if ($price !== '' && (!is_numeric($price) || (float)$price < 0)) {
            $errors[] = 'السعر يجب أن يكون رقماً صحيحاً أو اتركه فارغاً.';
        }
        if (!in_array($availability, ['available', 'limited', 'unavailable'], true)) {
            $availability = 'available';
        }

        if (empty($errors)) {
            $price_val = ($price !== '') ? $price : null;
            if ($service) {
                $stmt = $db->prepare('UPDATE services SET title = ?, category_id = ?, description = ?, price = ?, price_note = ?, icon = ?, availability = ?, featured = ? WHERE id = ?');
                $stmt->execute([$title, $category_id ?: null, $description, $price_val, $price_note, $icon, $availability, $featured, $id]);
                flash('success', 'تم تحديث الخدمة بنجاح.');
            } else {
                $stmt = $db->prepare('INSERT INTO services (title, category_id, description, price, price_note, icon, availability, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$title, $category_id ?: null, $description, $price_val, $price_note, $icon, $availability, $featured]);
                flash('success', 'تمت إضافة الخدمة بنجاح.');
            }
            redirect('services.php');
        }
    }
}

$form = $service ?? ['title' => '', 'category_id' => 0, 'description' => '', 'price' => '', 'price_note' => '', 'icon' => 'fa-briefcase', 'availability' => 'available', 'featured' => 0];
?>
<?php require __DIR__ . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <h2 class="page-title"><?= $service ? 'تعديل الخدمة' : 'إضافة خدمة جديدة' ?></h2>
        <p class="page-sub"><?= $service ? 'قم بتحديث تفاصيل الخدمة' : 'أدخل تفاصيل الخدمة الرقمية' ?></p>
    </div>
    <a href="services.php" class="btn btn-ghost"><i class="fa-solid fa-arrow-right"></i> عودة للقائمة</a>
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

<div class="panel form-panel">
    <form method="post" action="" class="admin-form">
        <?= csrf_field() ?>
        <div class="form-grid">
            <div class="form-group">
                <label for="title">اسم الخدمة *</label>
                <input type="text" id="title" name="title" class="form-control" value="<?= e($_POST['title'] ?? $form['title']) ?>" placeholder="مثال: اختبار اختراق وتقييم ثغرات" required>
            </div>
            <div class="form-group">
                <label for="category_id">التصنيف (اختياري)</label>
                <select id="category_id" name="category_id" class="form-select">
                    <option value="0">بدون تصنيف</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= (int)($_POST['category_id'] ?? $form['category_id']) === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="description">وصف الخدمة *</label>
            <textarea id="description" name="description" rows="5" class="form-control" required><?= e($_POST['description'] ?? $form['description']) ?></textarea>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="price">السعر بالدولار (اتركه فارغاً = عند الطلب)</label>
                <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" value="<?= e($_POST['price'] ?? $form['price'] ?? '') ?>" dir="ltr" placeholder="500">
            </div>
            <div class="form-group">
                <label for="price_note">ملاحظة السعر (مثال: شهرياً / عند الطلب)</label>
                <input type="text" id="price_note" name="price_note" class="form-control" value="<?= e($_POST['price_note'] ?? $form['price_note'] ?? '') ?>" placeholder="تبدأ من">
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label>أيقونة الخدمة</label>
                <div class="icon-picker">
                    <?php foreach ($icons as $ic): ?>
                        <label class="icon-option">
                            <input type="radio" name="icon" value="<?= e($ic) ?>" <?= ($_POST['icon'] ?? $form['icon']) === $ic ? 'checked' : '' ?>>
                            <i class="fa-solid <?= e($ic) ?>"></i>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="form-group">
                <label for="availability">حالة التوفر</label>
                <select id="availability" name="availability" class="form-select">
                    <?php foreach (['available' => 'متوفر', 'limited' => 'كمية محدودة', 'unavailable' => 'غير متاح'] as $k => $v): ?>
                        <option value="<?= $k ?>" <?= ($_POST['availability'] ?? $form['availability']) === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <label class="checkbox-line">
            <input type="checkbox" name="featured" value="1" <?= ($_POST['featured'] ?? $form['featured']) ? 'checked' : '' ?>>
            <span><i class="fa-solid fa-bolt"></i> عرض الخدمة كأكثر طلباً</span>
        </label>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> <?= $service ? 'حفظ التعديلات' : 'إضافة الخدمة' ?></button>
            <a href="services.php" class="btn btn-ghost">إلغاء</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>