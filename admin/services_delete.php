<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
    flash('error', 'معرّف خدمة غير صالح.');
    redirect('services.php');
}

$db = db();
$stmt = $db->prepare('SELECT id FROM services WHERE id = ?');
$stmt->execute([$id]);
if (!$stmt->fetch()) {
    flash('error', 'الخدمة غير موجودة.');
    redirect('services.php');
}

$db->prepare('DELETE FROM services WHERE id = ?')->execute([$id]);
flash('success', 'تم حذف الخدمة بنجاح.');
redirect('services.php');