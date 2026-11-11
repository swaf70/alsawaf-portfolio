<?php
require_once __DIR__ . '/auth.php';
$flashes = get_flashes();
$nav_active = $nav_active ?? '';
$admin_name = $admin_user['full_name'] ?: $admin_user['username'];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= admin_title($page_title ?? 'لوحة التحكم') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php if (defined('INLINE_ASSETS')): ?>
        <style id="app-inline-admin-css"><?= $INLINE['css_admin'] ?? '' ?></style>
    <?php else: ?>
        <link rel="stylesheet" href="<?= e(base_url('admin/assets/css/admin.css?v=3')) ?>">
    <?php endif; ?>
</head>
</head>
<body class="admin-body">

<aside class="sidebar" id="sidebar">
    <div class="sidebar-head">
        <span class="sidebar-logo"><i class="fa-solid fa-shield-halved"></i></span>
        <span class="sidebar-brand">لوحة التحكم</span>
        <button class="sidebar-close" id="sidebarClose"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <nav class="sidebar-nav">
        <a href="<?= e(base_url('admin/index.php')) ?>" class="side-link <?= $nav_active === 'dashboard' ? 'active' : '' ?>"><i class="fa-solid fa-gauge-high"></i> لوحة المعلومات</a>
        <a href="<?= e(base_url('admin/projects.php')) ?>" class="side-link <?= $nav_active === 'projects' ? 'active' : '' ?>"><i class="fa-solid fa-diagram-project"></i> المشاريع</a>
        <a href="<?= e(base_url('admin/services.php')) ?>" class="side-link <?= $nav_active === 'services' ? 'active' : '' ?>"><i class="fa-solid fa-briefcase"></i> الخدمات</a>
        <a href="<?= e(base_url('admin/categories.php')) ?>" class="side-link <?= $nav_active === 'categories' ? 'active' : '' ?>"><i class="fa-solid fa-folder-tree"></i> التصنيفات</a>
        <a href="<?= e(base_url('admin/messages.php')) ?>" class="side-link <?= $nav_active === 'messages' ? 'active' : '' ?>"><i class="fa-solid fa-envelope"></i> الرسائل <span class="side-badge" id="msgBadge"></span></a>
        <a href="<?= e(base_url('admin/users.php')) ?>" class="side-link <?= $nav_active === 'users' ? 'active' : '' ?>"><i class="fa-solid fa-users"></i> المستخدمون</a>
        <a href="<?= e(base_url('admin/digitalplat.php')) ?>" class="side-link <?= $nav_active === 'digitalplat' ? 'active' : '' ?>"><i class="fa-solid fa-earth-africa"></i> DigitalPlat API</a>
        <a href="<?= e(base_url('admin/settings.php')) ?>" class="side-link <?= $nav_active === 'settings' ? 'active' : '' ?>"><i class="fa-solid fa-sliders"></i> الإعدادات</a>
        <hr class="side-divider">
        <a href="<?= e(base_url('admin/change_password.php')) ?>" class="side-link <?= $nav_active === 'password' ? 'active' : '' ?>"><i class="fa-solid fa-key"></i> تغيير كلمة المرور</a>
        <a href="<?= e(base_url('index.php')) ?>" class="side-link" target="_blank"><i class="fa-solid fa-globe"></i> عرض الموقع</a>
        <a href="<?= e(base_url('admin/logout.php')) ?>" class="side-link side-logout"><i class="fa-solid fa-right-from-bracket"></i> تسجيل الخروج</a>
    </nav>
    <div class="sidebar-foot">
        <span class="foot-avatar"><i class="fa-solid fa-user-shield"></i></span>
        <div>
            <strong><?= e($admin_name) ?></strong>
            <small>مدير الموقع</small>
        </div>
    </div>
</aside>

<div class="admin-main">
    <header class="admin-topbar">
        <button class="menu-btn" id="menuBtn"><i class="fa-solid fa-bars"></i></button>
        <div class="topbar-title">
            <span class="online-dot"></span>
            <span><?= e($page_title ?? 'لوحة المعلومات') ?></span>
        </div>
        <div class="topbar-actions">
            <a href="<?= e(base_url('admin/logout.php')) ?>" class="topbar-link"><i class="fa-solid fa-right-from-bracket"></i> خروج</a>
        </div>
    </header>
    <main class="admin-content">
        <?php if ($flashes): ?>
            <div class="flash-area">
                <?php foreach ($flashes as $f): ?>
                    <div class="flash flash-<?= e($f['type']) ?>"><i class="fa-solid <?= $f['type'] === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check' ?>"></i> <?= e($f['message']) ?><button class="flash-x" onclick="this.parentElement.remove()">&times;</button></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>