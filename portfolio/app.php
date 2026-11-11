<?php
/**
 * app.php — نقطة التشغيل الواحدة (Single-file launcher)
 * ----------------------------------------------------------
 * هذا الملف هو المدخل الوحيد: افتحه في المتصفح وستعمل الصفحات كلها
 * من خلاله. التصميم (CSS) والسلوكيات (JS) يُضمّنان داخل الصفحة مباشرة
 * من هذا الملف، وبقية الصفحات تُستدعى عبر مسارات مثل:
 *
 *   http://yourhost/app.php            ← الرئيسية
 *   http://yourhost/app.php?p=login    ← تسجيل الدخول / حساب جديد
 *   http://yourhost/app.php?p=logout   ← خروج
 *   http://yourhost/app.php?p=admin    ← لوحة التحكم (للمدير فقط)
 *   http://yourhost/app.php?p=admin/projects  ← إدارة المشاريع
 *
 * بقية الملفات تظل موجودة بجانبه، ويمكن فتحها مباشرة أيضاً.
 */

define('INLINE_ASSETS', true);

/* تحميل التصميم والسلوكيات لضمّها داخل نص الصفحة */
$INLINE = [];
$INLINE['css_public'] = (string)@file_get_contents(__DIR__ . '/assets/css/style.css');
$INLINE['css_login']  = (string)@file_get_contents(__DIR__ . '/assets/css/login.css');
$INLINE['css_admin']  = (string)@file_get_contents(__DIR__ . '/admin/assets/css/admin.css');
$INLINE['js_public']  = (string)@file_get_contents(__DIR__ . '/assets/js/main.js');
$INLINE['js_admin']   = (string)@file_get_contents(__DIR__ . '/admin/assets/js/admin.js');

/* ---------- معرّف المسارات ---------- */
$routes = [
    ''                    => 'index.php',
    'home'                => 'index.php',
    'index'               => 'index.php',
    'login'               => 'login.php',
    'logout'              => 'logout.php',
    'admin'               => 'admin/index.php',
    'admin/index'         => 'admin/index.php',
    'admin/projects'      => 'admin/projects.php',
    'admin/projects_form' => 'admin/projects_form.php',
    'admin/services'      => 'admin/services.php',
    'admin/services_form' => 'admin/services_form.php',
    'admin/categories'    => 'admin/categories.php',
    'admin/messages'      => 'admin/messages.php',
    'admin/users'         => 'admin/users.php',
    'admin/digitalplat'   => 'admin/digitalplat.php',
    'admin/settings'      => 'admin/settings.php',
    'admin/change_password' => 'admin/change_password.php',
];

$p = (string)($_GET['p'] ?? '');

if (isset($routes[$p])) {
    $file = $routes[$p];
} elseif ($p !== '' && strpos($p, 'admin/') === 0) {
    $candidate = 'admin/' . basename($p) . '.php';
    $file = is_file(__DIR__ . '/' . $candidate) ? $candidate : 'index.php';
} else {
    $file = 'index.php';
}

$target = __DIR__ . '/' . $file;
if (!is_file($target)) {
    $file   = 'index.php';
    $target = __DIR__ . '/' . $file;
}

require $target;