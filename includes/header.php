<?php
require_once __DIR__ . '/functions.php';

$page_title = $page_title ?? setting('site_name', 'Portfolio');
$site_name  = setting('site_name', 'م. محمد الصواف');
$active     = $active ?? '';
$flashes    = get_flashes();
$me_view    = current_user();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e(setting('hero_tagline', 'مهندس شبكات وأمن معلومات — حلول تقنية متكاملة')) ?>">
    <title><?= e($page_title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php if (defined('INLINE_ASSETS')): ?>
        <style id="app-inline-css"><?= $INLINE['css_public'] ?? '' ?></style>
    <?php else: ?>
        <link rel="stylesheet" href="<?= e(base_url('assets/css/style.css?v=4')) ?>">
    <?php endif; ?>
</head>
<body data-aos="off">
<script>document.documentElement.classList.add('js');</script>
<noscript><style>.preloader{display:none;}</style></noscript>

<div class="preloader" id="preloader">
    <div class="preloader-spinner"><span></span><span></span><span></span></div>
</div>

<header class="navbar" id="navbar">
    <div class="container nav-inner">
        <a href="#home" class="brand">
            <span class="brand-mark"><i class="fa-solid fa-shield-halved"></i></span>
            <span class="brand-text"><?= e($site_name) ?></span>
        </a>
        <nav class="nav-menu" id="navMenu">
            <a href="#home" class="nav-link <?= $active === 'home' ? 'active' : '' ?>" data-id="home">الرئيسية</a>
            <a href="#about" class="nav-link <?= $active === 'about' ? 'active' : '' ?>" data-id="about">من أنا</a>
            <a href="#portfolio" class="nav-link <?= $active === 'portfolio' ? 'active' : '' ?>" data-id="portfolio">أعمالي</a>
            <a href="#services" class="nav-link <?= $active === 'services' ? 'active' : '' ?>" data-id="services">الخدمات</a>
            <a href="#contact" class="nav-link <?= $active === 'contact' ? 'active' : '' ?>" data-id="contact">تواصل معي</a>
        </nav>
        <div class="nav-actions">
            <?php if ($me_view): ?>
                <span class="nav-userchip" title="<?= e($me_view['full_name']) ?>">
                    <i class="fa-solid <?= $me_view['role'] === 'admin' ? 'fa-user-shield' : 'fa-user' ?>"></i>
                    <?= e(mb_substr($me_view['full_name'] ?: $me_view['username'], 0, 14)) ?>
                </span>
                <?php if ($me_view['role'] === 'admin'): ?>
                    <a href="<?= e(defined('INLINE_ASSETS') ? base_url('app.php?p=admin/index') : base_url('admin/index.php')) ?>" class="btn btn-ghost btn-sm">لوحة التحكم</a>
                <?php endif; ?>
                <a href="<?= e(defined('INLINE_ASSETS') ? base_url('app.php?p=logout') : base_url('logout.php')) ?>" class="btn btn-ghost btn-sm"><i class="fa-solid fa-right-from-bracket"></i> خروج</a>
            <?php else: ?>
                <a href="<?= e(defined('INLINE_ASSETS') ? base_url('app.php?p=login') : base_url('login.php')) ?>" class="btn btn-ghost btn-sm"><i class="fa-solid fa-right-to-bracket"></i> تسجيل الدخول</a>
            <?php endif; ?>
            <button class="nav-toggle" id="navToggle" aria-label="القائمة"><i class="fa-solid fa-bars"></i></button>
        </div>
    </div>
</header>

<?php if ($flashes): ?>
    <div class="flash-container" id="flashContainer">
        <?php foreach ($flashes as $f): ?>
            <div class="flash flash-<?= e($f['type']) ?>"><?= e($f['message']) ?><button class="flash-close" onclick="this.parentElement.remove()">&times;</button></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>