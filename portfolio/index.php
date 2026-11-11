<?php
require_once __DIR__ . '/includes/functions.php';

$settings = [];
try {
    $settings = db()->query('SELECT setting_key, setting_value FROM settings')->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (Throwable $e) {
    $settings = [];
}

function setting_or(array $rows, string $key, string $default): string {
    return $rows[$key] ?? $default;
}

$db      = db();
$cats    = $db->query('SELECT id, name, slug, icon FROM categories ORDER BY id')->fetchAll();
$projects = $db->query(
    'SELECT p.*, c.name AS category_name, c.slug AS category_slug
     FROM projects p
     JOIN categories c ON c.id = p.category_id
     ORDER BY p.featured DESC, p.created_at DESC'
)->fetchAll();
$services = $db->query('SELECT * FROM services ORDER BY featured DESC, created_at DESC')->fetchAll();

$page_title = setting_or($settings, 'site_name', 'الرئيسية');
$site_name  = setting_or($settings, 'site_name', 'م. محمد الصواف');
$hero_title = setting_or($settings, 'hero_title', 'مهندس شبكات وأمن معلومات');
$typing     = json_decode(setting_or($settings, 'hero_typing', '[]'), true);
if (!is_array($typing)) {
    $typing = ['مهندس شبكات', 'خبير أمن سيبراني'];
}

$skills = [
    ['name' => 'إدارة وتصميم الشبكات', 'level' => 95],
    ['name' => 'أمن المعلومات ومكافحة الاختراق', 'level' => 90],
    ['name' => 'الجدران النارية و VPN', 'level' => 88],
    ['name' => 'تطوير الويب (PHP / JS)', 'level' => 85],
    ['name' => 'قواعد البيانات (MySQL)', 'level' => 82],
    ['name' => 'أمان تطبيقات الويب', 'level' => 92],
];

$stats = [
    ['value' => 3, 'suffix' => '+', 'label' => 'سنوات خبرة'],
    ['value' => 120, 'suffix' => '+', 'label' => 'مشروع منفّذ'],
    ['value' => 85, 'suffix' => '+', 'label' => 'عميل سعيد'],
    ['value' => 10, 'suffix' => '+', 'label' => 'شهادة احترافية'],
];

$testimonials = [
    ['name' => 'محمد العتيبي', 'role' => 'مدير تقنية المعلومات — شركة أفق', 'text' => 'تعامل محترف وأمانة عالية في العمل. سكّب شبكتنا بالكامل مع جدار حماية متقدم بدون أي توقف للعمل.', 'icon' => 'fa-user-tie'],
    ['name' => 'نورة القحطاني', 'role' => 'مؤسسة متجر إلكتروني', 'text' => 'بنى لي المتجر من الصفر بسرعة وجودة عالية، والدعم الفني مستمر. أنصح فيه بشدة لأي مشروع تقني.', 'icon' => 'fa-businesswoman'],
    ['name' => 'خالد الرشيد', 'role' => 'مطور برمجيات', 'text' => 'فحص أمني دقيق كشف ثغرات لم نكن نتوقعها، مع تقرير واضح وخطوات إصلاح عملية. خبرة حقيقية بالأمن.', 'icon' => 'fa-user-astronaut'],
];

$availability_map = [
    'available'   => ['متوفر', 'badge-available'],
    'limited'     => ['كمية محدودة', 'badge-limited'],
    'unavailable' => ['غير متاح', 'badge-unavailable'],
];

require __DIR__ . '/includes/header.php';
?>

<!-- ============ HERO ============ -->
<section class="hero" id="home" data-aos="fade-up">
    <div class="hero-bg" aria-hidden="true">
        <div class="hero-grid-overlay"></div>
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>
    <div class="container hero-inner">
        <div class="hero-content">
            <span class="hero-badge"><i class="fa-solid fa-code"></i> متاح لاستقبال المشاريع</span>
            <h1 class="hero-title">
                أهلاً، أنا
                <span class="gradient-text"><?= e($site_name) ?></span>
            </h1>
            <div class="hero-type">
                <span class="hero-type-label">أعمل كـ</span>
                <span class="typer" id="typer" data-words='<?= e(json_encode($typing, JSON_UNESCAPED_UNICODE)) ?>'></span><span class="typer-caret"></span>
            </div>
            <p class="hero-desc"><?= e(setting_or($settings, 'hero_tagline', '')) ?></p>
            <div class="hero-actions">
                <a href="#portfolio" class="btn btn-primary"><i class="fa-solid fa-briefcase"></i> تصفح أعمالي</a>
                <a href="#contact" class="btn btn-ghost"><i class="fa-solid fa-paper-plane"></i> تواصل معي</a>
            </div>
            <div class="hero-tech">
                <span>PHP</span><span>MySQL</span><span>JavaScript</span><span>Linux</span><span>CCNA</span><span>CEH</span>
            </div>
        </div>
        <div class="hero-visual">
            <div class="code-card" id="codeCard">
                <div class="code-card-head">
                    <span class="dot red"></span><span class="dot yellow"></span><span class="dot green"></span>
                    <span class="code-card-title">network-audit.php</span>
                </div>
                <div class="code-card-body">
                    <pre><code><span class="c-kw">function</span> <span class="c-fn">secureNetwork</span>(<span class="c-var">$network</span>) {
    <span class="c-kw">foreach</span> (<span class="c-var">$network</span> <span class="c-kw">as</span> <span class="c-var">$device</span>) {
        <span class="c-fn">applyFirewall</span>(<span class="c-var">$device</span>);
        <span class="c-fn">enableEncryption</span>(<span class="c-var">$device</span>);
        <span class="c-fn">runPenTest</span>(<span class="c-var">$device</span>);
    }
    <span class="c-kw">return</span> <span class="c-str">'Network SECURED ✓'</span>;
}</code></pre>
                </div>
                <div class="code-card-status"><span class="pulse-dot"></span> Status: <span class="c-ok">All Systems Protected</span></div>
            </div>
            <div class="hero-orbit">
                <span class="orbit-ring r1"></span><span class="orbit-ring r2"></span>
                <div class="orbit-core"><i class="fa-solid fa-shield-halved"></i></div>
            </div>
            <div class="float-chip chip-1"><i class="fa-solid fa-lock"></i> AES-256</div>
            <div class="float-chip chip-2"><i class="fa-solid fa-network-wired"></i> 99.9% Uptime</div>
            <div class="float-chip chip-3"><i class="fa-solid fa-bug-slash"></i> Zero Critical</div>
        </div>
    </div>
    <div class="hero-scroll"><a href="#about"><span></span></a></div>
</section>

<!-- ============ STATS ============ -->
<section class="stats-band" id="statsBand">
    <div class="container stats-grid">
        <?php foreach ($stats as $s): ?>
            <div class="stat-item" data-aos="zoom-in">
                <span class="stat-value"><span class="counter" data-target="<?= (int)$s['value'] ?>">0</span><span class="stat-suffix"><?= e($s['suffix']) ?></span></span>
                <span class="stat-label"><?= e($s['label']) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ============ ABOUT ============ -->
<section class="section about" id="about">
    <div class="container">
        <div class="section-head" data-aos="fade-down">
            <span class="section-tag">من أنا</span>
            <h2 class="section-title">أجمع بين <span class="gradient-text">الهندسة</span> و<span class="gradient-text">الأمن</span> في كل مشروع</h2>
            <p class="section-sub">تعرّف على مسيرتي المهنية وخبراتي التقنية</p>
        </div>
        <div class="about-grid">
            <div class="about-visual" data-aos="fade-left">
                <div class="about-frame">
                    <div class="about-card-main">
                        <div class="about-avatar"><i class="fa-solid fa-user-shield"></i></div>
                        <h3><?= e($site_name) ?></h3>
                        <p><?= e($hero_title) ?></p>
                        <a href="#contact" class="btn btn-primary btn-sm">تواصل معي <i class="fa-solid fa-arrow-left"></i></a>
                    </div>
                </div>
                <div class="about-badge-exp">
                    <span class="exp-num">3+</span>
                    <span class="exp-label">سنوات<br>خبرة</span>
                </div>
            </div>
            <div class="about-content" data-aos="fade-right">
                <p class="about-text"><?= e(setting_or($settings, 'about_1', '')) ?></p>
                <p class="about-text"><?= e(setting_or($settings, 'about_2', '')) ?></p>
                <div class="about-tags">
                    <span><i class="fa-solid fa-check"></i> عقد صيانة ودعم</span>
                    <span><i class="fa-solid fa-check"></i> تسليم بالوقت</span>
                    <span><i class="fa-solid fa-check"></i> تقرير أمني شفاف</span>
                </div>
                <h4 class="skills-title">مهاراتي التقنية</h4>
                <div class="skills">
                    <?php foreach ($skills as $sk): ?>
                        <div class="skill" data-aos="fade-right">
                            <div class="skill-head"><span><?= e($sk['name']) ?></span><span class="skill-pct" data-level="<?= (int)$sk['level'] ?>">0%</span></div>
                            <div class="skill-bar"><div class="skill-fill" data-level="<?= (int)$sk['level'] ?>"></div></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============ PORTFOLIO ============ -->
<section class="section portfolio" id="portfolio">
    <div class="container">
        <div class="section-head" data-aos="fade-down">
            <span class="section-tag">معرض الأعمال</span>
            <h2 class="section-title">مشاريع <span class="gradient-text">نفّذتها</span> باحترافية</h2>
            <p class="section-sub">استعرض أعمالي في تطوير الويب والشبكات والأمن السيبراني</p>
        </div>
        <div class="portfolio-toolbar" data-aos="fade-up">
            <div class="filter-buttons" id="filterButtons">
                <button class="filter-btn active" data-filter="all">الكل</button>
                <?php foreach ($cats as $c): ?>
                    <button class="filter-btn" data-filter="<?= e($c['slug']) ?>">
                        <i class="fa-solid <?= e($c['icon']) ?>"></i> <?= e($c['name']) ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="projectSearch" placeholder="ابحث عن مشروع...">
            </div>
        </div>
        <div class="projects-grid" id="projectsGrid">
            <?php if ($projects): ?>
                <?php foreach ($projects as $p): ?>
                    <article class="project-card" data-aos="zoom-in"
                             data-category="<?= e($p['category_slug']) ?>"
                             data-title="<?= e($p['title']) ?>"
                             data-description="<?= e(mb_substr($p['description'] ?? '', 0, 500)) ?>"
                             data-image="<?= e($p['image'] ?: '') ?>"
                             data-category-name="<?= e($p['category_name']) ?>">
                        <div class="project-thumb">
                            <?php if ($p['image']): ?>
                                <img src="<?= e($p['image']) ?>" alt="<?= e($p['title']) ?>" loading="lazy">
                            <?php else: ?>
                                <div class="project-noimg"><i class="fa-solid fa-diagram-project"></i></div>
                            <?php endif; ?>
                            <div class="project-overlay">
                                <span class="project-cat-badge"><i class="fa-solid <?= e($p['category_icon'] ?? 'fa-folder') ?>"></i> <?= e($p['category_name']) ?></span>
                                <div class="project-actions">
                                    <button class="btn-icon project-view" title="عرض التفاصيل"><i class="fa-solid fa-eye"></i></button>
                                </div>
                            </div>
                            <?php if ($p['featured']): ?><span class="project-featured"><i class="fa-solid fa-star"></i> مميز</span><?php endif; ?>
                        </div>
                        <div class="project-info">
                            <h3 class="project-title"><?= e($p['title']) ?></h3>
                            <p class="project-desc"><?= e(mb_substr($p['description'] ?? '', 0, 90)) ?>…</p>
                            <span class="project-tech"><?= e($p['category_name']) ?></span>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <p class="empty-state" id="projectsEmpty" hidden><i class="fa-solid fa-folder-open"></i> لا توجد نتائج مطابقة للبحث</p>
    </div>
</section>

<!-- ============ SERVICES ============ -->
<section class="section services" id="services">
    <div class="container">
        <div class="section-head" data-aos="fade-down">
            <span class="section-tag">الخدمات والمنتجات</span>
            <h2 class="section-title">حلول و<span class="gradient-text">خدمات رقمية</span> عند الطلب</h2>
            <p class="section-sub">اختر الخدمة المناسبة واحصل على عرض سعر خلال 24 ساعة</p>
        </div>
        <div class="services-grid">
            <?php if ($services): ?>
                <?php foreach ($services as $srv): ?>
                    <?php [$avail_label, $avail_class] = $availability_map[$srv['availability']] ?? ['متوفر', 'badge-available']; ?>
                    <article class="service-card <?= $srv['featured'] ? 'service-featured' : '' ?>" data-aos="fade-up">
                        <?php if ($srv['featured']): ?><span class="service-popular">الأكثر طلباً</span><?php endif; ?>
                        <div class="service-icon"><i class="fa-solid <?= e($srv['icon']) ?>"></i></div>
                        <span class="service-badge <?= $avail_class ?>"><?= e($avail_label) ?></span>
                        <h3 class="service-title"><?= e($srv['title']) ?></h3>
                        <p class="service-desc"><?= e($srv['description']) ?></p>
                        <div class="service-price">
                            <?php if ($srv['price'] !== null): ?>
                                <span class="price-num">$<?= number_format((float)$srv['price'], 0) ?></span>
                                <?php if ($srv['price_note']): ?><span class="price-note"><?= e($srv['price_note']) ?></span><?php endif; ?>
                            <?php else: ?>
                                <span class="price-num price-request">عند الطلب</span>
                            <?php endif; ?>
                        </div>
                        <a href="#contact" class="btn btn-outline btn-block order-btn"
                           data-service="<?= e($srv['title']) ?>"><i class="fa-solid fa-cart-plus"></i> اطلب الخدمة</a>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ============ TESTIMONIALS ============ -->
<section class="section testimonials" id="testimonials">
    <div class="container">
        <div class="section-head" data-aos="fade-down">
            <span class="section-tag">آراء العملاء</span>
            <h2 class="section-title">ماذا يقول <span class="gradient-text">عملائي</span> عني؟</h2>
        </div>
        <div class="testimonials-grid">
            <?php foreach ($testimonials as $t): ?>
                <figure class="testimonial-card" data-aos="zoom-in">
                    <div class="testimonial-stars"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
                    <blockquote>"<?= e($t['text']) ?>"</blockquote>
                    <figcaption>
                        <span class="testimonial-avatar"><i class="fa-solid <?= e($t['icon']) ?>"></i></span>
                        <div><strong><?= e($t['name']) ?></strong><small><?= e($t['role']) ?></small></div>
                    </figcaption>
                </figure>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============ CONTACT ============ -->
<section class="section contact" id="contact">
    <div class="container">
        <div class="section-head" data-aos="fade-down">
            <span class="section-tag">تواصل معي</span>
            <h2 class="section-title">لنبدأ <span class="gradient-text">مشروعك</span> اليوم</h2>
            <p class="section-sub">أرسل لي رسالتك وسأرد خلال 24 ساعة</p>
        </div>
        <div class="contact-grid">
            <div class="contact-info" data-aos="fade-left">
                <div class="info-card">
                    <span class="info-icon"><i class="fa-solid fa-envelope"></i></span>
                    <div><h4>البريد الإلكتروني</h4><p dir="ltr"><?= e(setting_or($settings, 'contact_email', '')) ?></p></div>
                </div>
                <div class="info-card">
                    <span class="info-icon"><i class="fa-solid fa-phone"></i></span>
                    <div><h4>الهاتف / واتساب</h4><p dir="ltr"><?= e(setting_or($settings, 'contact_phone', '')) ?></p></div>
                </div>
                <div class="info-card">
                    <span class="info-icon"><i class="fa-solid fa-location-dot"></i></span>
                    <div><h4>الموقع</h4><p><?= e(setting_or($settings, 'contact_location', '')) ?></p></div>
                </div>
                <a href="<?= e(setting_or($settings, 'social_whatsapp', '#')) ?>" target="_blank" rel="noopener" class="btn btn-whatsapp btn-block">
                    <i class="fa-brands fa-whatsapp"></i> تواصل واتساب مباشرة
                </a>
            </div>
            <form class="contact-form" id="contactForm" data-aos="fade-right" novalidate>
                <div class="form-row">
                    <div class="form-group">
                        <label for="cName">الاسم *</label>
                        <input type="text" id="cName" name="name" class="form-control" placeholder="اسمك الكامل" required>
                        <span class="field-error"></span>
                    </div>
                    <div class="form-group">
                        <label for="cEmail">البريد الإلكتروني *</label>
                        <input type="email" id="cEmail" name="email" class="form-control" placeholder="name@example.com" required dir="ltr">
                        <span class="field-error"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="cSubject">الموضوع *</label>
                    <input type="text" id="cSubject" name="subject" class="form-control" placeholder="موضوع الرسالة" required>
                    <span class="field-error"></span>
                </div>
                <div class="form-group">
                    <label for="cMessage">الرسالة *</label>
                    <textarea id="cMessage" name="message" class="form-control" rows="6" placeholder="اكتب تفاصيل مشروعك أو استفسارك هنا..." required></textarea>
                    <span class="field-error"></span>
                </div>
                <div class="form-terms">
                    <i class="fa-solid fa-lock"></i> رسالتك مشفرة وسيتم الرد عليها عبر البريد الإلكتروني بأمان.
                </div>
                <button type="submit" class="btn btn-primary btn-block submit-btn" id="contactSubmit">
                    <span class="btn-label"><i class="fa-solid fa-paper-plane"></i> إرسال الرسالة</span>
                    <span class="btn-loading" hidden><i class="fa-solid fa-spinner fa-spin"></i> جار الإرسال...</span>
                </button>
            </form>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>