<?php
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

$admin_user = current_user();

function admin_title(string $page): void {
    echo e(APP_NAME . ' | ' . $page);
}