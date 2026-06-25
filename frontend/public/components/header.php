<?php
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$user = $_SESSION['rf_user'] ?? null;
$role = $user['role'] ?? 'owner';

// Get current page name from URI
$pageName = trim($requestUri, '/');
if (empty($pageName)) $pageName = 'dashboard';
$pageDisplay = ucwords(str_replace('-', ' ', $pageName));
?>
<header class="h-16 bg-white/80 backdrop-blur-lg border-b border-blue-100 flex items-center justify-between px-4 lg:px-8 sticky top-0 z-20">
    <div class="flex items-center gap-4">
        <button onclick="document.getElementById('sidebar').classList.toggle('-translate-x-full')" class="lg:hidden p-2 text-slate-500 hover:bg-blue-50 rounded-xl"><i class="fas fa-bars"></i></button>
        <div class="hidden md:flex items-center gap-2 text-sm text-slate-500">
            <span class="capitalize"><?php echo $role; ?> Portal</span>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-slate-800 font-medium"><?php echo $pageDisplay; ?></span>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <div class="hidden md:flex items-center bg-blue-50 rounded-xl px-3 py-1.5">
            <i class="fas fa-search text-slate-400 text-sm mr-2"></i>
            <input type="text" placeholder="Search..." class="bg-transparent border-none outline-none text-sm w-40 text-slate-700 placeholder-slate-400">
        </div>
        <button onclick="event.preventDefault();localStorage.removeItem('rf_token');window.location.href='/signin'" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-colors" title="Logout"><i class="fas fa-sign-out-alt"></i></button>
    </div>
</header>
