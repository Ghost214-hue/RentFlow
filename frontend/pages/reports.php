<?php
session_start();
$token = $_COOKIE['rf_token'] ?? $_SESSION['rf_token'] ?? null;
if (!$token) { header('Location: ../public/signin.php'); exit; }
require_once __DIR__ . '/../../backend/app/Core/Env.php';
// Load correct .env for localhost vs production
$isLocalhost = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1'], true) ||
               str_starts_with($_SERVER['HTTP_HOST'] ?? '', 'localhost:');
$envPath = $isLocalhost
    ? __DIR__ . '/../../.env'
    : (file_exists(__DIR__ . '/../../.env.production') ? __DIR__ . '/../../.env.production' : __DIR__ . '/../../.env');
\App\Core\Env::load($envPath);
require_once __DIR__ . '/../../backend/app/Core/JWT.php';
$jwt = new \App\Core\JWT();
$user = $jwt->decode($token);
if (!$user) { header('Location: ../public/signin.php'); exit; }
$_SESSION['rf_user'] = $user;
$role = $user['role'] ?? 'owner';
require_once __DIR__ . '/../includes/base-path-fix.php';
$basePath = getBasePath();

// Determine active tab
$activeTab = $_GET['tab'] ?? 'tenancy_vacancy';
$validTabs = ['tenancy_vacancy', 'financial', 'bills', 'complaints'];
if (!in_array($activeTab, $validTabs)) {
    $activeTab = 'tenancy_vacancy';
}

// Load the report tab component
require_once __DIR__ . '/../public/components/report_tab.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - RentaFlow</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($basePath); ?>/css/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-50/30 min-h-screen font-sans text-slate-800 flex flex-col lg:flex-row">
    <?php include __DIR__ . '/../public/components/sidebar.php'; ?>
    <div class="flex-1 flex flex-col min-h-screen">
        <?php include __DIR__ . '/../public/components/header.php'; ?>
        <main class="flex-1 overflow-y-auto p-4 lg:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Reports</h1>
                    <p class="text-slate-500 mt-1">Analytics and performance insights</p>
                </div>
            </div>

            <!-- Tab Navigation -->
            <?php report_tab($basePath, $activeTab); ?>

            <!-- Shared Helpers (must be before included report views) -->
            <div id="toast" class="fixed bottom-6 right-6 z-50 hidden px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2"></div>
            <script>
            // Calculate base path
            let BASE = window.location.pathname;
            const frontendPagesIndex = BASE.indexOf('/frontend/pages/');
            if (frontendPagesIndex !== -1) {
                BASE = BASE.substring(0, frontendPagesIndex);
            } else {
                BASE = BASE.replace(/\/[^\/]*$/, '');
            }
            const API = BASE + '/api';
            // Get token from cookie (primary auth method) or localStorage (fallback)
    const cookies = document.cookie.split(';');
    let cookieToken = '';
    for (let c of cookies) {
        const [k, v] = c.trim().split('=');
        if (k === 'rf_token') { cookieToken = decodeURIComponent(v); break; }
    }
    const token = cookieToken || localStorage.getItem('rf_token') || '<?php echo $token; ?>';
            const headers = token ? {'Authorization':'Bearer '+token, 'Content-Type':'application/json'} : {'Content-Type':'application/json'};

            async function apiRequest(url, options = {}) {
                const controller = new AbortController();
                const timeout = setTimeout(() => controller.abort(), 15000);
                try {
                    const res = await fetch(url, { ...options, headers, signal: controller.signal });
                    clearTimeout(timeout);
                    const text = await res.text();
                    let data;
                    try { data = JSON.parse(text); } catch(e) { throw new Error('Server error'); }
                    if (!res.ok) {
                        if (res.status === 401) {
                            localStorage.removeItem('rf_token');
                            window.location.href = BASE + '/signin';
                        }
                        throw new Error(data.error || 'Request failed');
                    }
                    return data;
                } catch (e) {
                    clearTimeout(timeout);
                    if (e.name === 'AbortError') {
                        throw new Error('Request timed out');
                    }
                    throw e;
                }
            }

            function fmtCurrency(n) { return 'KES ' + Number(n).toLocaleString(); }
            function toast(msg, type='success') {
                const el = document.getElementById('toast');
                const colors = { success:'bg-gradient-to-r from-emerald-500 to-emerald-600', error:'bg-gradient-to-r from-red-500 to-red-600', info:'bg-gradient-to-r from-blue-500 to-blue-600' };
                const icons = { success:'fa-check-circle', error:'fa-exclamation-circle', info:'fa-info-circle' };
                el.className = `fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2 ${colors[type]||colors.success}`;
                el.innerHTML = `<i class="fas ${icons[type]||icons.success}"></i>${msg}`;
                el.classList.remove('hidden');
                setTimeout(() => el.classList.add('hidden'), 3000);
            }

            function getLastSixMonths() {
                const months = [];
                const now = new Date();
                for (let i = 5; i >= 0; i--) {
                    const dt = new Date(now.getFullYear(), now.getMonth() - i, 1);
                    const year = dt.getFullYear();
                    const month = String(dt.getMonth() + 1).padStart(2, '0');
                    months.push(`${year}-${month}`);
                }
                return months;
            }

            function formatMonthLabel(ym) {
                const [year, month] = ym.split('-');
                return new Date(Number(year), Number(month) - 1, 1).toLocaleDateString('en-GB', { month: 'short', year: '2-digit' });
            }

            function getStatusBadge(status) {
                const s = (status || '').toLowerCase();
                const map = {
                    'paid': 'bg-emerald-100 text-emerald-700',
                    'completed': 'bg-emerald-100 text-emerald-700',
                    'confirmed': 'bg-emerald-100 text-emerald-700',
                    'partial': 'bg-amber-100 text-amber-700',
                    'pending': 'bg-slate-100 text-slate-600',
                    'overdue': 'bg-red-100 text-red-700',
                    'open': 'bg-blue-100 text-blue-700',
                    'in-progress': 'bg-amber-100 text-amber-700',
                    'resolved': 'bg-emerald-100 text-emerald-700',
                    'failed': 'bg-red-100 text-red-700',
                    'vacant': 'bg-slate-100 text-slate-600',
                    'occupied': 'bg-emerald-100 text-emerald-700',
                    'active': 'bg-emerald-100 text-emerald-700',
                    'terminated': 'bg-red-100 text-red-700',
                    'low': 'bg-emerald-100 text-emerald-700',
                    'medium': 'bg-amber-100 text-amber-700',
                    'high': 'bg-red-100 text-red-700',
                };
                const cls = map[s] || 'bg-slate-100 text-slate-600';
                return `<span class="px-2 py-0.5 rounded-full text-xs font-medium ${cls}">${status || 'N/A'}</span>`;
            }

            function renderPagination(containerId, meta, onPageChange, options = {}) {
                const container = document.getElementById(containerId);
                if (!container) return;
                container.innerHTML = '';
                const template = document.getElementById('__rf_pagination_template');
                if (!template) return;
                const wrapper = template.firstElementChild.cloneNode(true);
                const pageSpan = wrapper.querySelector('[data-role="pages"]');
                pageSpan.textContent = `Page ${meta.page} of ${meta.total_pages} · ${meta.total} items`;
                const setDisabled = (sel, disabled) => { if (sel) sel.disabled = !!disabled; };
                const first = wrapper.querySelector('[data-action="first"]');
                const prev = wrapper.querySelector('[data-action="prev"]');
                const next = wrapper.querySelector('[data-action="next"]');
                const last = wrapper.querySelector('[data-action="last"]');
                setDisabled(first, meta.page <= 1);
                setDisabled(prev, meta.page <= 1);
                setDisabled(next, meta.page >= meta.total_pages);
                setDisabled(last, meta.page >= meta.total_pages);
                first.addEventListener('click', () => onPageChange(1));
                prev.addEventListener('click', () => onPageChange(Math.max(1, meta.page - 1)));
                next.addEventListener('click', () => onPageChange(Math.min(meta.total_pages, meta.page + 1)));
                last.addEventListener('click', () => onPageChange(meta.total_pages));
                const selectEl = wrapper.querySelector('[data-role="per-page-select"]');
                if (selectEl) selectEl.closest('div').style.display = 'none';
                container.appendChild(wrapper);
            }
            </script>

            <!-- Pagination Template (shared across reports) -->
            <?php include __DIR__ . '/../public/components/pagination.php'; ?>

            <!-- Report Content Panel -->
            <div role="tabpanel" id="panel-<?php echo htmlspecialchars($activeTab); ?>" aria-labelledby="tab-<?php echo htmlspecialchars($activeTab); ?>">
                <?php
                $reportPage = __DIR__ . '/reports/' . str_replace('_', '', $activeTab) . '.php';
                if (file_exists($reportPage)) {
                    include $reportPage;
                } else {
                    echo '<div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-12 text-center text-slate-400">
                            <i class="fas fa-file-excel text-4xl mb-3"></i>
                            <p>Report module not found.</p>
                          </div>';
                }
                ?>
            </div>
        </main>
    </div>
</body>
</html>