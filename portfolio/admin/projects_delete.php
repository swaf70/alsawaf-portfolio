<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
    flash('error', 'معرّف مشروع غير صالح.');
    redirect('projects.php');
}

$db = db();
$stmt = $db->prepare('SELECT * FROM projects WHERE id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row) {
    flash('error', 'المشروع غير موجود.');
    redirect('projects.php');
}

try {
    $db->beginTransaction();
    if ($row['image']) {
        delete_upload($row['image']);
    }
    $db->prepare('DELETE FROM projects WHERE id = ?')->execute([$id]);
    $db->commit();
    flash('success', 'تم حذف المشروع وصورته بنجاح.');
} catch (Throwable $e) {
    $db->rollBack();
    flash('error', 'تعذر حذف المشروع: ' . $e->getMessage());
}

redirect('projects.php');
