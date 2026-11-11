<?php
$page_title = 'إعدادات الموقع';
$nav_active = 'settings';
require_once __DIR__ . '/includes/auth.php';

$db = db();
$errors = [];

$fields = [
    'site_name'      => 'اسم الموقع (الاسم الظاهر)',
    'hero_title'     => 'العنوان الوظيفي الرئيسي',
    'hero_tagline'   => 'الوصف الترويجي (الجملة التعريفية)',
    'about_1'        => 'فقرة «من أنا» الأولى',
    'about_2'        => 'فقرة «من أنا» الثانية',
    'contact_email'  => 'البريد الإلكتروني للتواصل',
    'contact_phone'  => 'رقم الهاتف / واتساب',
    'contact_location' => 'موقع العمل',
    'social_github'  => 'رابط GitHub',
    'social_linkedin'=> 'رابط LinkedIn',
        'social_whatsapp'=> 'رابط واتساب المباشر',
    'footer_text'    => 'نص التذييل (حقوق النشر)',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'انتهت صلاحية الجلسة، أعد المحاولة.';
    } else {
        $stmt = $db->prepare('UPDATE settings SET setting_value = ? WHERE setting_key = ?');
        foreach ($fields as $key => $label) {
            $val = trim($_POST[$key] ?? '');
            if (mb_strlen($val) > 5000) {
                $val = mb_substr($val, 0, 5000);
            }
            $stmt->execute([$val, $key]);
        }
    }

    if (!$errors) {
        flash('success', 'تم حفظ الإعدادات بنجاح.');
        redirect('settings.php');
    }
}

$currentRow = db()->query('SELECT setting_key, setting_value FROM settings')->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<?php require __DIR__ . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <h2 class="page-title">إعدادات الموقع</h2>
        <p class="page-sub">تحكم كامل في النصوص والمعلومات الظاهرة في الواجهة العامة</p>
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

<form method="post" action="settings.php" class="panel form-panel">
    <?= csrf_field() ?>

    <div class="settings-cols">
        <div class="settings-col">
            <h3 class="settings-col-title"><i class="fa-solid fa-user"></i> الهوية الشخصية</h3>
            <?php foreach (['site_name', 'hero_title', 'hero_tagline', 'about_1', 'about_2'] as $k): ?>
                <div class="form-group">
                    <label for="st_<?= $k ?>"><?= e($fields[$k]) ?></label>
                    <?php if ($k === 'about_1' || $k === 'about_2'): ?>
                        <textarea id="st_<?= $k ?>" name="<?= $k ?>" rows="4" class="form-control"><?= e($currentRow[$k] ?? '') ?></textarea>
                    <?php else: ?>
                        <input type="text" id="st_<?= $k ?>" name="<?= $k ?>" class="form-control" value="<?= e($currentRow[$k] ?? '') ?>">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="settings-col">
            <h3 class="settings-col-title"><i class="fa-solid fa-address-book"></i> معلومات التواصل</h3>

            <div class="form-group">
                <label for="st_contact_email"><?= e($fields['contact_email']) ?></label>
                <input type="email" id="st_contact_email" name="contact_email" class="form-control" value="<?= e($currentRow['contact_email'] ?? '') ?>" dir="ltr">
            </div>
            <div class="form-group">
                <label for="st_contact_phone"><?= e($fields['contact_phone']) ?></label>
                <input type="text" id="st_contact_phone" name="contact_phone" class="form-control" value="<?= e($currentRow['contact_phone'] ?? '') ?>" dir="ltr">
            </div>
            <div class="form-group">
                <label for="st_contact_location"><?= e($fields['contact_location']) ?></label>
                <input type="text" id="st_contact_location" name="contact_location" class="form-control" value="<?= e($currentRow['contact_location'] ?? '') ?>">
            </div>
        </div>
    </div>

    <h3 class="settings-col-title sep"><i class="fa-solid fa-share-nodes"></i> حساباتي على المنصات</h3>
    <div class="settings-cols">
        <div class="settings-col">
            <?php foreach (['social_github', 'social_linkedin'] as $k): ?>
                <div class="form-group">
                    <label for="st_<?= $k ?>"><?= e($fields[$k]) ?></label>
                    <input type="url" id="st_<?= $k ?>" name="<?= $k ?>" class="form-control" value="<?= e($currentRow[$k] ?? '') ?>" dir="ltr">
                </div>
            <?php endforeach; ?>
        </div>
        <div class="settings-col">
            <?php foreach (['social_whatsapp'] as $k): ?>
                <div class="form-group">
                    <label for="st_<?= $k ?>"><?= e($fields[$k]) ?></label>
                    <input type="url" id="st_<?= $k ?>" name="<?= $k ?>" class="form-control" value="<?= e($currentRow[$k] ?? '') ?>" dir="ltr">
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="form-group">
        <label for="st_footer_text"><?= e($fields['footer_text']) ?></label>
        <input type="text" id="st_footer_text" name="footer_text" class="form-control" value="<?= e($currentRow['footer_text'] ?? '') ?>">
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> حفظ جميع الإعدادات</button>
        <a href="<?= e(base_url('index.php')) ?>" class="btn btn-ghost" target="_blank"><i class="fa-solid fa-eye"></i> معاينة الموقع</a>
    </div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>