<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'portfolio_db');
define('DB_USER', 'root');
define('DB_PASS', '');

define('APP_NAME', 'م. محمد الصواف | مهندس شبكات وأمن معلومات');
define('UPLOADS_DIR', __DIR__ . '/../uploads');
define('UPLOADS_URL', 'uploads');
define('MAX_FILE_SIZE', 2097152); // 2MB
define('ALLOWED_IMG_EXT', ['jpg', 'jpeg', 'png', 'webp', 'gif']);
define('ALLOWED_IMG_MIME', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

define('ADMIN_USERNAME_DEFAULT', 'admin');
define('ADMIN_PASSWORD_DEFAULT', 'admin123');

date_default_timezone_set('Asia/Riyadh');