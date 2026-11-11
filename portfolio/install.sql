-- ============================================================
-- Personal Portfolio & Digital Services Showcase Platform
-- Database Schema + Sample Data
-- Charset: utf8mb4 (supports full Arabic)
-- ============================================================

CREATE DATABASE IF NOT EXISTS portfolio_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE portfolio_db;

-- ------------------------------------------------------------
-- users
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(50) NOT NULL DEFAULT '',
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  phone VARCHAR(30) DEFAULT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  last_login DATETIME DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- categories
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(100) NOT NULL,
  icon VARCHAR(60) NOT NULL DEFAULT 'fa-folder',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- projects
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS projects (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(150) NOT NULL,
  category_id INT UNSIGNED NOT NULL,
  description TEXT,
  image VARCHAR(255) DEFAULT NULL,
  link VARCHAR(255) DEFAULT NULL,
  featured TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_category (category_id),
  CONSTRAINT fk_projects_category
    FOREIGN KEY (category_id) REFERENCES categories (id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- services
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS services (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(150) NOT NULL,
  category_id INT UNSIGNED DEFAULT NULL,
  description TEXT,
  price DECIMAL(10,2) DEFAULT NULL,
  price_note VARCHAR(100) DEFAULT NULL,
  icon VARCHAR(60) NOT NULL DEFAULT 'fa-briefcase',
  availability ENUM('available','limited','unavailable') NOT NULL DEFAULT 'available',
  featured TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_category (category_id),
  CONSTRAINT fk_services_category
    FOREIGN KEY (category_id) REFERENCES categories (id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- settings
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS settings (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  setting_key VARCHAR(50) NOT NULL,
  setting_value TEXT,
  PRIMARY KEY (id),
  UNIQUE KEY uq_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- messages (contact form submissions)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS messages (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  subject VARCHAR(200) DEFAULT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SAMPLE DATA
-- ============================================================

INSERT INTO categories (id, name, slug, icon) VALUES
(1, 'مواقع ويب', 'websites', 'fa-globe'),
(2, 'تطبيقات جوال', 'mobile-apps', 'fa-mobile-screen'),
(3, 'أدوات شبكات', 'network-tools', 'fa-network-wired'),
(4, 'أدوات أمن وسايبر', 'security-tools', 'fa-shield-halved');

INSERT INTO projects (id, title, category_id, description, image, link, featured) VALUES
(1, 'منصة تعليم إلكتروني ذكية', 1,
 'منصة متكاملة لإدارة الدورات التدريبية، تشمل نظام تسجيل الطلاب، اختبارات تفاعلية، ولوحة تحكم للمعلمين مع تقارير تقدّم لحظية. مبنية بأمان عالٍ وحماية كاملة للبيانات.',
 'assets/img/projects/e-learning.svg', 'https://example.com/e-learning', 1),
(4, 'سكربت فحص شبكي متقدم (Network Scanner)', 3,
 'أداة مسح شبكي احترافية تكتشف الأجهزة والمنافذ المفتوحة والخوادم داخل الشبكة، مع واجهة تقارير ملونة وتصدير النتائج بصيغ متعددة.',
 'assets/img/projects/network-scanner.svg', 'https://github.com/example/network-scanner', 1),
(6, 'أداة فحص ثغرات الويب', 4,
 'أداة أمنية تحاكي هجمات الحقن والفكسنغ لاكتشاف الثغرات في تطبيقات الويب، مع تقرير أولويات وعن اقتراحات إصلاح.',
 'assets/img/projects/security-scanner.svg', 'https://github.com/example/security-scanner', 1),
(7, 'تطبيق مراسلة مشفّرة من طرف إلى طرف', 2,
 'تطبيق مراسلة فورية بتشفير كامل من الطرف إلى الطرف، يحمي خصوصية المحادثات والملفات من أي اعتراض.',
 'assets/img/projects/secure-chat.svg', 'https://github.com/example/secure-chat', 0);

INSERT INTO services (id, title, category_id, description, price, price_note, icon, availability, featured) VALUES
(1, 'تصميم مواقع ومدونات احترافية', 1,
 'تصميم وتطوير مواقع متجاوبة وسريعة ومتوافقة مع محركات البحث، بهوية بصرية مميزة.',
 150.00, 'تبدأ من', 'fa-globe', 'available', 0),
(2, 'متجر إلكتروني متكامل', 1,
 'متجر شامل مع بوابات دفع، إدارة مخزون، وأنظمة شحن وتوصيل.',
 500.00, 'تبدأ من', 'fa-cart-shopping', 'limited', 0),
(3, 'تهيئة شبكات الشركات (VPN / Firewall)', 3,
 'تصميم وتهيئة الشبكات الداخلية، جدران الحماية، شبكات VPN الآمنة، وفصل الشبكات حسب الأقسام.',
 800.00, 'تبدأ من', 'fa-network-wired', 'available', 1),
(4, 'اختبار اختراق وتقييم الثغرات', 4,
 'فحص شامل لأنظمتك وتطبيقاتك لاكتشاف الثغرات قبل اختراقها، مع تقرير تفصيلي ومراحل إصلاح موثقة.',
 1200.00, 'تبدأ من', 'fa-shield-halved', 'available', 1),
(5, 'فحص أمني وتدقيق أنظمة', 4,
 'مراجعة أمنية كاملة للبنية التحتية بنظاميات التشغيل والتطبيقات وفق أفضل المعايير العالمية.',
 NULL, 'عند الطلب', 'fa-magnifying-glass-chart', 'limited', 0),
(6, 'صيانة وحماية الخوادم', 3,
 'مراقبة دورية، تحديثات أمنية مستمرة، نسخ احتياطي تلقائي، واستعادة فورية عند الأعطال.',
 250.00, 'شهرياً', 'fa-server', 'available', 0),
(7, 'تطبيقات جوال مخصصة', 2,
 'تطوير تطبيقات موبايل لأنظمة iOS وAndroid بأداء عالٍ وتجربة مستخدم مميزة.',
 700.00, 'تبدأ من', 'fa-mobile-screen', 'unavailable', 0),
(8, 'استشارات ودعم فني', NULL,
 'جلسات استشارية عن بعد في مجال الشبكات والأمن السيبراني وحلول البرمجيات.',
 100.00, 'بالساعة', 'fa-headset', 'available', 0);

INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'م. محمد الصواف'),
('hero_title', 'مهندس شبكات وأمن معلومات'),
('hero_typing', '["مهندس شبكات","خبير أمن سيبراني","مطوّرويب","مستشار تقني"]'),
('hero_tagline', 'أساعد الشركات والأفراد على بناء بنية تقنية آمنة، سريعة، وقابلة للتوسع — من تصميم المواقع إلى تحصين الشبكات.'),
('about_1', 'أنا م. محمد الصواف، مهندس شبكات وأمن معلومات بخبرة تتجاوز 3 سنوات في تصميم وإدارة البنى التحتية للشبكات، وبناء الحلول البرمجية الآمنة. أعمل على دمج هندسة الشبكات مع تطوير البرمجيات لتقديم حلول متكاملة تبدأ من التخطيط وتنتهي بالحماية.'),
('about_2', 'أؤمن أن الأمان ليس خطوة إضافية، بل أساس أي نظام. لذلك أركز على بناء أنظمة آمنة بالتصميم (Security by Design)، مع الالتزام بأفضل الممارسات العالمية في التشفير وحماية البيانات.'),
('contact_email', 'swaf8315@gmail.com'),
('contact_phone', '+967 771792570'),
('contact_location', 'الرياض، المملكة العربية السعودية'),
('social_github', 'https://github.com/khaledsalmi'),
('social_linkedin', 'https://linkedin.com/in/khaledsalmi'),
('social_twitter', ''),
('social_whatsapp', 'https://wa.me/967771792570'),
('footer_text', 'م. محمد الصواف © 2026 — جميع الحقوق محفوظة.');

INSERT INTO messages (id, name, email, subject, message, is_read) VALUES
(1, 'شركة التقدم التقني', 'hr@taqaddum.sa', 'طلب تنفيذ متجر إلكتروني',
 'مرحباً، نرغب بتنفيذ متجر إلكتروني متكامل مع بوابات دفع محلية. هل يمكنك التواصل معنا لمعرفة التفاصيل والجدول الزمني؟', 0),
(2, 'م. سارة العتيبي', 'sara@example.com', 'استشارة أمنية',
 'أحتاج جلسة استشارية لمراجعة إعدادات الشبكة في شركتنا والتحقق من جدار الحماية، يرجى إبلاغي بمواعيدك المتاحة.', 0);