<?php
// Get base path from environment
$basePath = rtrim((string) ($_ENV['BASE_PATH'] ?? getenv('BASE_PATH') ?? '/RentalFlow'), '/');
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$user = $_SESSION['rf_user'] ?? null;
$role = $user['role'] ?? 'owner';

// Get current page name from URI
$pageName = trim(str_replace($basePath, '', $requestUri), '/');
if (empty($pageName)) $pageName = 'dashboard';
$pageDisplay = ucwords(str_replace('-', ' ', $pageName));
?>
<header class="h-16 bg-white/80 backdrop-blur-lg border-b border-blue-100 flex items-center justify-between px-3 sm:px-4 lg:px-8 sticky top-0 z-20">
    <div class="flex items-center gap-2 sm:gap-4">
        <button onclick="toggleSidebar()" class="lg:hidden p-2 text-slate-500 hover:bg-blue-50 rounded-xl"><i class="fas fa-bars text-lg"></i></button>
        <div class="hidden sm:flex items-center gap-2 text-sm text-slate-500">
            <span class="capitalize"><?php echo $role; ?> Portal</span>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-slate-800 font-medium truncate max-w-[120px] md:max-w-none"><?php echo $pageDisplay; ?></span>
        </div>
    </div>
    <div class="flex items-center gap-1 sm:gap-2">
        <div class="hidden sm:flex items-center bg-blue-50 rounded-xl px-3 py-1.5">
            <i class="fas fa-search text-slate-400 text-sm mr-2"></i>
            <input type="text" placeholder="Search..." class="bg-transparent border-none outline-none text-sm w-20 md:w-40 text-slate-700 placeholder-slate-400">
        </div>
        <button onclick="event.preventDefault();logout()" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-colors" title="Logout"><i class="fas fa-sign-out-alt"></i></button>
    </div>
</header>
<script>
function deleteRfTokenCookie() {
    document.cookie = 'rf_token=; path=/; max-age=0; expires=Thu, 01 Jan 1970 00:00:00 GMT; SameSite=Strict';
}

async function logout() {
    try {
        await fetch('<?php echo $basePath; ?>/api/auth/logout', {
            method: 'POST',
            headers: {'Content-Type':'application/json'}
        });
    } catch (err) {
        console.warn('Logout request failed', err);
    }
    deleteRfTokenCookie();
    if (window.sessionStorage) window.sessionStorage.clear();
    window.location.href = '<?php echo $basePath; ?>/signin';
}

window.addEventListener('pageshow', (event) => {
    const backNav = event.persisted || (performance.getEntriesByType && performance.getEntriesByType('navigation').length && performance.getEntriesByType('navigation')[0].type === 'back_forward');
    if (backNav) {
        // User navigated back - force fresh page load to re-validate session
        window.location.reload();
    }
});
</script>