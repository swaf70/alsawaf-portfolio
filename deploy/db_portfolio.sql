-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: portfolio_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `portfolio_db`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `portfolio_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `portfolio_db`;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icon` varchar(60) NOT NULL DEFAULT 'fa-folder',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'مواقع ويب','websites','fa-globe','2026-09-07 22:15:30'),(2,'تطبيقات جوال','mobile-apps','fa-mobile-screen','2026-09-07 22:15:30'),(3,'أدوات شبكات','network-tools','fa-network-wired','2026-09-07 22:15:30'),(4,'أدوات أمن وسايبر','security-tools','fa-shield-halved','2026-09-07 22:15:30');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
INSERT INTO `messages` VALUES (1,'شركة التقدم التقني','hr@taqaddum.sa','طلب تنفيذ متجر إلكتروني','مرحباً، نرغب بتنفيذ متجر إلكتروني متكامل مع بوابات دفع محلية. هل يمكنك التواصل معنا لمعرفة التفاصيل والجدول الزمني؟',0,'2026-09-07 22:15:30'),(2,'م. سارة العتيبي','sara@example.com','استشارة أمنية','أحتاج جلسة استشارية لمراجعة إعدادات الشبكة في شركتنا والتحقق من جدار الحماية، يرجى إبلاغي بمواعيدك المتاحة.',0,'2026-09-07 22:15:30');
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category_id`),
  CONSTRAINT `fk_projects_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
INSERT INTO `projects` VALUES (1,'منصة تعليم إلكتروني ذكية',1,'منصة متكاملة لإدارة الدورات التدريبية، تشمل نظام تسجيل الطلاب، اختبارات تفاعلية، ولوحة تحكم للمعلمين مع تقارير تقدّم لحظية. مبنية بأمان عالٍ وحماية كاملة للبيانات.','assets/img/projects/e-learning.svg','https://example.com/e-learning',1,'2026-09-07 22:15:30','2026-09-07 22:15:30'),(2,'نظام حضور ذكي بالتعرف على الوجه',2,'تطبيق موبايل يستخدم الذكاء الاصطناعي للتعرف على الوجوه لحضور الموظفين والطلاب، مع تقارير تأخير وانصراف فورية وإشعارات تلقائية.','assets/img/projects/attendance-app.svg','https://example.com/attendance',0,'2026-09-07 22:15:30','2026-09-07 22:15:30'),(3,'متجر إلكتروني متكامل',1,'متجر إلكتروني بإدارة كاملة للمنتجات والمخزون والمدفوعات، يدعم بوابات دفع متعددة، مع تصميم متجاوب وتجربة تسوق سلسة.','assets/img/projects/store.svg','https://example.com/store',0,'2026-09-07 22:15:30','2026-09-07 22:15:30'),(4,'سكربت فحص شبكي متقدم (Network Scanner)',3,'أداة مسح شبكي احترافية تكتشف الأجهزة والمنافذ المفتوحة والخوادم داخل الشبكة، مع واجهة تقارير ملونة وتصدير النتائج بصيغ متعددة.','assets/img/projects/network-scanner.svg','https://github.com/example/network-scanner',1,'2026-09-07 22:15:30','2026-09-07 22:15:30'),(5,'لوحة مراقبة الخوادم والبنية التحتية',3,'نظام مراقبة لحظي للخوادم والسويتشات مع تنبيهات ذكية عند الأعطال، ورسوم بيانية لاستخدام المعالج والذاكرة والشبكة.','assets/img/projects/server-monitor.svg','https://example.com/monitor',0,'2026-09-07 22:15:30','2026-09-07 22:15:30'),(6,'أداة فحص ثغرات الويب',4,'أداة أمنية تحاكي هجمات الحقن والفكسنغ لاكتشاف الثغرات في تطبيقات الويب، مع تقرير أولويات وعن اقتراحات إصلاح.','assets/img/projects/security-scanner.svg','https://github.com/example/security-scanner',1,'2026-09-07 22:15:30','2026-09-07 22:15:30'),(7,'تطبيق مراسلة مشفّرة من طرف إلى طرف',2,'تطبيق مراسلة فورية بتشفير كامل من الطرف إلى الطرف، يحمي خصوصية المحادثات والملفات من أي اعتراض.','assets/img/projects/secure-chat.svg','https://github.com/example/secure-chat',0,'2026-09-07 22:15:30','2026-09-07 22:15:30'),(8,'نظام إدارة مخزون للمستودعات',1,'لوحة تحكم ويب لإدارة المخزون والموردين والفواتير مع بحث فوري وتقارير تحليلية، وربط مباشر بماسحات الباركود.','assets/img/projects/inventory.svg','https://example.com/inventory',0,'2026-09-07 22:15:30','2026-09-07 22:15:30');
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `services` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `category_id` int(10) unsigned DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `price_note` varchar(100) DEFAULT NULL,
  `icon` varchar(60) NOT NULL DEFAULT 'fa-briefcase',
  `availability` enum('available','limited','unavailable') NOT NULL DEFAULT 'available',
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category_id`),
  CONSTRAINT `fk_services_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `services`
--

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
INSERT INTO `services` VALUES (1,'تصميم مواقع ومدونات احترافية',1,'تصميم وتطوير مواقع متجاوبة وسريعة ومتوافقة مع محركات البحث، بهوية بصرية مميزة.',150.00,'تبدأ من','fa-globe','available',0,'2026-09-07 22:15:30'),(2,'متجر إلكتروني متكامل',1,'متجر شامل مع بوابات دفع، إدارة مخزون، وأنظمة شحن وتوصيل.',500.00,'تبدأ من','fa-cart-shopping','limited',0,'2026-09-07 22:15:30'),(3,'تهيئة شبكات الشركات (VPN / Firewall)',3,'تصميم وتهيئة الشبكات الداخلية، جدران الحماية، شبكات VPN الآمنة، وفصل الشبكات حسب الأقسام.',800.00,'تبدأ من','fa-network-wired','available',1,'2026-09-07 22:15:30'),(4,'اختبار اختراق وتقييم الثغرات',4,'فحص شامل لأنظمتك وتطبيقاتك لاكتشاف الثغرات قبل اختراقها، مع تقرير تفصيلي ومراحل إصلاح موثقة.',1200.00,'تبدأ من','fa-shield-halved','available',1,'2026-09-07 22:15:30'),(5,'فحص أمني وتدقيق أنظمة',4,'مراجعة أمنية كاملة للبنية التحتية بنظاميات التشغيل والتطبيقات وفق أفضل المعايير العالمية.',NULL,'عند الطلب','fa-magnifying-glass-chart','limited',0,'2026-09-07 22:15:30'),(6,'صيانة وحماية الخوادم',3,'مراقبة دورية، تحديثات أمنية مستمرة، نسخ احتياطي تلقائي، واستعادة فورية عند الأعطال.',250.00,'شهرياً','fa-server','available',0,'2026-09-07 22:15:30'),(7,'تطبيقات جوال مخصصة',2,'تطوير تطبيقات موبايل لأنظمة iOS وAndroid بأداء عالٍ وتجربة مستخدم مميزة.',700.00,'تبدأ من','fa-mobile-screen','unavailable',0,'2026-09-07 22:15:30'),(8,'استشارات ودعم فني',NULL,'جلسات استشارية عن بعد في مجال الشبكات والأمن السيبراني وحلول البرمجيات.',100.00,'بالساعة','fa-headset','available',0,'2026-09-07 22:15:30');
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'site_name','م. خالد السلمي'),(2,'hero_title','مهندس شبكات وأمن معلومات'),(3,'hero_typing','[\"مهندس شبكات\",\"خبير أمن سيبراني\",\"مطوّرويب\",\"مستشار تقني\"]'),(4,'hero_tagline','أساعد الشركات والأفراد على بناء بنية تقنية آمنة، سريعة، وقابلة للتوسع — من تصميم المواقع إلى تحصين الشبكات.'),(5,'about_1','أنا خالد السلمي، مهندس شبكات وأمن معلومات بخبرة تتجاوز 7 سنوات في تصميم وإدارة البنى التحتية للشبكات، وبناء الحلول البرمجية الآمنة. أعمل على دمج هندسة الشبكات مع تطوير البرمجيات لتقديم حلول متكاملة تبدأ من التخطيط وتنتهي بالحماية.'),(6,'about_2','أؤمن أن الأمان ليس خطوة إضافية، بل أساس أي نظام. لذلك أركز على بناء أنظمة آمنة بالتصميم (Security by Design)، مع الالتزام بأفضل الممارسات العالمية في التشفير وحماية البيانات.'),(7,'contact_email','contact@khaled-dev.com'),(8,'contact_phone','+966 55 000 0000'),(9,'contact_location','الرياض، المملكة العربية السعودية'),(10,'social_github','https://github.com/khaledsalmi'),(11,'social_linkedin','https://linkedin.com/in/khaledsalmi'),(12,'social_twitter','https://x.com/khaledsalmi'),(13,'social_whatsapp','https://wa.me/966550000000'),(14,'footer_text','م. خالد السلمي © 2026 — جميع الحقوق محفوظة.');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `last_login` datetime DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_username` (`username`),
  UNIQUE KEY `uq_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','$2y$10$vNIfqV2tx6maLPWE0XIl3e23ADbRWlCiIVXQ.f5MfTdnSqylQo/Km','admin','2026-09-09 17:28:57','مدير الموقع','admin@localhost',NULL,'2026-09-07 22:15:31'),(3,'','$2y$10$y756FtCZ.Kl/jF2bis2KuOrG/I44ScmCt9L6e05Im8dGqV.LMPmx.','user','2026-09-08 23:36:34','محمد عبدالقادر','swaf8315@gmail.com','+967 771792570','2026-09-08 20:36:34');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-09 17:30:07
