<?php
$page_title = 'DigitalPlat — النطاقات';
$nav_active = 'digitalplat';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/dp_config.php';

/* ---------- API client (server-side only) ---------- */
function dp_http(string $method, string $path, ?array $payload = null, int $timeout = 25): array
{
    $url = DP_API_BASE . $path;
    $ua  = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36';
    $headers = ['Authorization: Bearer ' . DP_API_TOKEN, 'Accept: application/json'];

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => $timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => $ua,
            CURLOPT_HTTPHEADER     => $headers,
        ];
        if ($method === 'POST') {
            $opts[CURLOPT_POST] = true;
            if ($payload !== null) {
                $opts[CURLOPT_POSTFIELDS] = json_encode($payload);
                $opts[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
            }
        }
        curl_setopt_array($ch, $opts);
        $body = (string)curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $err  = curl_error($ch);
        curl_close($ch);
        return ['code' => $code, 'body' => $body, 'err' => $err];
    }

    $ctx = stream_context_create([
        'http' => [
            'method'  => $method,
            'timeout' => $timeout,
            'ignore_errors' => true,
            'header'  => 'User-Agent: ' . $ua . "\r\n" .
                         implode("\r\n", $headers) . "\r\n" .
                         ($payload !== null ? 'Content-Type: application/json' . "\r\n" : ''),
            'content' => $payload !== null ? json_encode($payload) : null,
        ],
    ]);
    $body = @file_get_contents($url, false, $ctx);
    return ['code' => (int)($http_response_header[0] ?? ''), 'body' => $body === false ? '' : $body, 'err' => $body === false ? 'تعذر الاتصال' : ''];
}

function dp_err_msg(array $res): string
{
    if ($res['err']) {
        return 'تعذر الاتصال بالـ API: ' . $res['err'];
    }
    $j = json_decode((string)$res['body'], true);
    if (is_array($j)) {
        if (!empty($j['error'])) {
            return is_string($j['error']) ? $j['error'] : json_encode($j['error']);
        }
        if (!empty($j['message'])) {
            return is_string($j['message']) ? $j['message'] : json_encode($j['message']);
        }
    }
    return 'استجابة غير متوقعة من الـ API (HTTP ' . $res['code'] . ').';
}

function dp_fmt_date(?string $d): string
{
    if (!$d || !preg_match('/^\d{8}$/', $d)) {
        return '—';
    }
    $t = strtotime(substr($d, 0, 4) . '-' . substr($d, 4, 2) . '-' . substr($d, 6, 2));
    return $t ? date('Y-m-d', $t) : '—';
}

function dp_days_left(?string $d): ?int
{
    if (!$d || !preg_match('/^\d{8}$/', $d)) {
        return null;
    }
    $t = strtotime(substr($d, 0, 4) . '-' . substr($d, 4, 2) . '-' . substr($d, 6, 2) . ' 23:59:59');
    return $t ? (int)floor(($t - time()) / 86400) : null;
}

/* ---------- Renew (real action via API) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'renew') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        flash('error', 'انتهت صلاحية الجلسة، أعد المحاولة.');
        redirect('digitalplat.php');
    }
    $domain = trim((string)($_POST['domain'] ?? ''));
    if ($domain === '') {
        flash('error', 'اسم النطاق غير صالح.');
        redirect('digitalplat.php');
    }
    $res = dp_http('POST', '/domains/' . rawurlencode($domain) . '/renew', ['renewal_type' => 'free', 'years' => 1]);
    if ($res['code'] === 200 || $res['code'] === 201) {
        flash('success', "تم إرسال طلب التجديد بنجاح للنطاق «$domain».");
    } else {
        flash('error', 'فشل التجديد: ' . dp_err_msg($res));
    }
    redirect('digitalplat.php');
}

/* ---------- Fetch domain list (60s cache) ---------- */
$domains  = [];
$apiError = null;
$lastFetch = null;
$cacheFile = sys_get_temp_dir() . '/dp_domains_' . md5(DP_API_TOKEN) . '.cache';
$refresh = isset($_GET['refresh']);

if (!$refresh && is_file($cacheFile) && (time() - (int)filemtime($cacheFile)) < 60) {
    $decoded = json_decode((string)@file_get_contents($cacheFile), true);
    if (isset($decoded['domains'], $decoded['at'])) {
        $domains  = (array)$decoded['domains'];
        $lastFetch = (int)$decoded['at'];
    }
}

if ($lastFetch === null) {
    $res = dp_http('GET', '/domains');
    if ($res['code'] === 200) {
        $j = json_decode((string)$res['body'], true);
        $domains = is_array($j) ? ($j['data'] ?? []) : [];
        @file_put_contents($cacheFile, json_encode(['domains' => $domains, 'at' => time()]));
        $lastFetch = time();
    } else {
        $apiError = dp_err_msg($res);
    }
}

$total      = count($domains);
$expiring   = 0;
$autoRenew  = 0;
$statusOk   = 0;
foreach ($domains as $d) {
    $days = dp_days_left($d['expires_at'] ?? null);
    if ($days !== null && $days <= 30) {
        $expiring++;
    }
    if (!empty($d['auto_renew'])) {
        $autoRenew++;
    }
    if (($d['status'] ?? '') === 'ok') {
        $statusOk++;
    }
}
?>
<?php require __DIR__ . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <h2 class="page-title">DigitalPlat — النطاقات</h2>
        <p class="page-sub">بيانات نطاقاتك في منصة DigitalPlat مباشرةً من الـ API (نافذة لقراءة فقط للمدير)</p>
    </div>
    <a href="<?= e(base_url('admin/digitalplat.php?refresh=1')) ?>" class="btn btn-ghost"><i class="fa-solid fa-rotate"></i> تحديث البيانات</a>
</div>

<div class="form-hint-box">
    <i class="fa-solid fa-shield-halved"></i>
    <div>هذه الصفحة محمية بصلاحية المدير وحدها. مفتاح الـ API محفوظ في جهة الخادم فقط ولا يصل أبداً إلى المتصفح.</div>
</div>

<?php if ($apiError): ?>
    <div class="alert-box">
        <li><i class="fa-solid fa-circle-exclamation"></i> <?= e($apiError) ?></li>
        <li><i class="fa-solid fa-circle-info"></i> تحقق من تشغيل الـ API وتحديث بياناتك أو أعد المحاولة لاحقاً.</li>
    </div>
<?php endif; ?>

<div class="stat-cards">
    <div class="stat-card">
        <div class="stat-card-icon blue"><i class="fa-solid fa-earth-africa"></i></div>
        <span class="stat-card-num"><?= (int)$total ?></span><span class="stat-card-label">نطاق</span>
        <small>إجمالي النطاقات في حسابك</small>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon red"><i class="fa-solid fa-clock"></i></div>
        <span class="stat-card-num"><?= (int)$expiring ?></span><span class="stat-card-label">تنتهي قريباً</span>
        <small>خلال 30 يوماً أو أقل</small>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon green"><i class="fa-solid fa-arrows-rotate"></i></div>
        <span class="stat-card-num"><?= (int)$autoRenew ?></span><span class="stat-card-label">تجديد تلقائي</span>
        <small>مفعّل على النطاقات</small>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon purple"><i class="fa-solid fa-circle-check"></i></div>
        <span class="stat-card-num"><?= (int)$statusOk ?></span><span class="stat-card-label">سليمة</span>
        <small>الحالة ok</small>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <h3><i class="fa-solid fa-layer-group"></i> قائمة النطاقات</h3>
        <?php if ($lastFetch): ?><small class="muted">آخر تحديث: <?= e(date('Y-m-d H:i:s', $lastFetch)) ?></small><?php endif; ?>
    </div>
    <div class="table-scroll">
        <table class="table">
            <thead>
                <tr>
                    <th>النطاق</th>
                    <th>الحالة</th>
                    <th>النوع</th>
                    <th>تاريخ الإنشاء</th>
                    <th>الانتهاء</th>
                    <th>الأيام المتبقية</th>
                    <th>التجديد التلقائي</th>
                    <th>خوادم الأسماء</th>
                    <th>عمليات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$domains): ?>
                    <tr>
                        <td colspan="9" class="td-empty">
                            <i class="fa-solid fa-box-open"></i>
                            <?= $apiError ? 'تعذر جلب البيانات' : 'لا توجد نطاقات في حسابك بعد' ?>
                        </td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($domains as $d): ?>
                <?php
                    $days = dp_days_left($d['expires_at'] ?? null);
                    $daysCls = $days === null || $days > 90 ? 'ok' : ($days <= 30 ? 'danger' : 'warning');
                    $renB = '—';
                    if (($d['slot_type'] ?? '') === 'free' && $days !== null && $days <= 150) {
                        $renewText = 'تنتهي في ' . dp_fmt_date($d['expires_at'])
                                   . ' النطاق مؤهل للتجديد المجاني ' . (($d['lifecycle_type'] ?? '') === 'permanent' ? 'مع بقاء مدى الحياة.' : 'لسنة إضافية.');
                        $renB = '<form method="post" action="digitalplat.php" class="inline-form">' . csrf_field()
                              . '<input type="hidden" name="action" value="renew">'
                              . '<input type="hidden" name="domain" value="' . e($d['domain']) . '">'
                              . '<button type="submit" class="btn-icon-sm edit confirm-submit" data-confirm-title="تأكيد التجديد" data-confirm-text="' . e($renewText) . '" title="تجديد مجاني"><i class="fa-solid fa-rotate-right"></i></button></form>';
                    }
                ?>
                <tr>
                    <td><span class="td-strong" dir="ltr"><?= e($d['domain']) ?></span></td>
                    <td>
                        <?php if (($d['status'] ?? '') === 'ok'): ?>
                            <span class="badge badge-available"><i class="fa-solid fa-circle-check"></i> سليم</span>
                        <?php else: ?>
                            <span class="badge badge-unavailable"><?= e($d['status'] ?: '—') ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (($d['slot_type'] ?? '') === 'free'): ?>
                            <span class="badge badge-new">مجاني</span>
                        <?php else: ?>
                            <span class="badge badge-feat">مدفوع</span>
                        <?php endif; ?>
                    </td>
                    <td class="muted"><?= e(dp_fmt_date($d['created_at'] ?? null)) ?></td>
                    <td class="td-strong"><?= e(dp_fmt_date($d['expires_at'] ?? null)) ?></td>
                    <td>
                        <?php if ($days === null): ?>
                            <span class="muted">—</span>
                        <?php elseif ($daysCls === 'danger'): ?>
                            <span class="badge badge-unavailable"><?= (int)$days ?> يوم</span>
                        <?php elseif ($daysCls === 'warning'): ?>
                            <span class="badge badge-limited"><?= (int)$days ?> يوم</span>
                        <?php else: ?>
                            <span class="muted"><?= (int)$days ?> يوم</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($d['auto_renew'])): ?>
                            <span class="badge badge-available"><i class="fa-solid fa-circle-check"></i> مفعّل</span>
                        <?php else: ?>
                            <span class="badge badge-normal">غير مفعّل</span>
                        <?php endif; ?>
                    </td>
                    <td class="muted" dir="ltr"><?= e(implode(', ', array_map('htmlspecialchars', (array)($d['nameservers'] ?? [])))) ?: '—' ?></td>
                    <td class="td-actions"><?= $renB ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($domains): ?>
<details class="panel">
    <summary class="panel-head" style="cursor:pointer; list-style:none;"><h3><i class="fa-solid fa-code"></i> الاستجابة الخام من الـ API</h3><i class="fa-solid fa-chevron-down muted"></i></summary>
    <pre style="direction:ltr; text-align:left; background:#f4f7fb; border:1px solid rgba(15,23,42,0.1); border-radius:12px; padding:16px; overflow:auto; max-height:420px; font-size:12px; line-height:1.6;"><?= e(json_encode($domains, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
</details>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>