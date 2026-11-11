<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    redirect(base_url('admin/index.php'));
}

redirect(base_url('login.php'));