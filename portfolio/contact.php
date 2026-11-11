<?php
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'طريقة الطلب غير مسموحة.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$name    = trim($input['name'] ?? '');
$email   = trim($input['email'] ?? '');
$subject = trim($input['subject'] ?? '');
$message = trim($input['message'] ?? '');

$errors = [];
if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
    $errors['name'] = 'يرجى إدخال الاسم بشكل صحيح.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
    $errors['email'] = 'يرجى إدخال بريد إلكتروني صحيح.';
}
if (mb_strlen($subject) < 3 || mb_strlen($subject) > 200) {
    $errors['subject'] = 'يرجى إدخال موضوع الرسالة.';
}
if (mb_strlen($message) < 10 || mb_strlen($message) > 5000) {
    $errors['message'] = 'الرسالة يجب أن تكون بين 10 و 5000 حرف.';
}

if ($errors) {
    http_response_code(422);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

try {
    $stmt = db()->prepare(
        'INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$name, $email, $subject, $message]);
    echo json_encode([
        'success' => true,
        'message' => 'تم إرسال رسالتك بنجاح! سأرد عليك خلال 24 ساعة.',
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'حدث خلل في الخادم، حاول مرة أخرى.']);
}