<?php
$basePath = '/RentaFlow'; // Hardcoded for this deployment
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$user = $_SESSION['rf_user'] ?? null;
$role = $user['role'] ?? 'owner';
$token = $_COOKIE['rf_token'] ?? $_SESSION['rf_token'] ?? null;

$ownerNav = [
    [$basePath . '/dashboard','fa-chart-line','Dashboard',''],
    [$basePath . '/properties','fa-building','Properties',''],
    [$basePath . '/houses','fa-home','Houses',''],
    [$basePath . '/tenants','fa-users','Tenants',''],
    [$basePath . '/caretakers','fa-user-shield','Caretakers',''],
    [$basePath . '/payments','fa-money-bill-wave','Payments',''],
    [$basePath . '/bills','fa-file-invoice-dollar','Bills',''],
    [$basePath . '/complaints','fa-exclamation-triangle','Complaints','complaintCount'],
    [$basePath . '/documents','fa-file-alt','Rules',''],
    [$basePath . '/reports','fa-chart-pie','Reports',''],
];

$caretakerNav = [
    [$basePath . '/dashboard','fa-chart-line','Dashboard',''],
    [$basePath . '/properties','fa-building','Properties',''],
    [$basePath . '/houses','fa-home','Houses',''],
    [$basePath . '/tenants','fa-users','Tenants',''],
    [$basePath . '/payments','fa-money-bill-wave','Payments',''],
    [$basePath . '/bills','fa-file-invoice-dollar','Bills',''],
    [$basePath . '/complaints','fa-exclamation-triangle','Complaints','complaintCount'],
    [$basePath . '/documents','fa-file-alt','Rules',''],
];

$tenantNav = [
    [$basePath . '/tenant-dashboard','fa-chart-line','Dashboard',''],
    [$basePath . '/tenant-profile','fa-user','My Profile',''],
    [$basePath . '/payments','fa-money-bill-wave','My Payments','paymentCount'],
    [$basePath . '/bills','fa-file-invoice-dollar','My Bills',''],
    [$basePath . '/complaints','fa-exclamation-triangle','My Complaints','complaintCount'],
    [$basePath . '/documents','fa-file-alt','Rules',''],
];

$nav = match($role) {
    'caretaker' => $caretakerNav,
    'tenant' => $tenantNav,
    default => $ownerNav,
};

function isActive($uri, $path) {
    return $uri === $path || strpos($uri, $path) === 0;
}
?>
<?php $nonav = ($requestUri === '/signin' || $requestUri === '/signup'); ?>
<!-- Mobile sidebar overlay -->
<aside id="sidebarMobile" class="lg:hidden fixed inset-y-0 left-0 z-40 w-64 bg-gradient-to-b from-blue-700 to-blue-900 shadow-2xl transform -translate-x-full transition-transform duration-300 ease-in-out flex flex-col min-h-screen">
    <div class="h-16 flex items-center px-5 border-b border-white/10">
        <div class="flex items-center gap-3">
            <img src="<?php echo $basePath; ?>/images/rentalflow-logo.png" alt="RentalFlow" class="h-8 w-auto object-contain" onerror="this.style.display='none'">
            <span class="font-bold text-lg text-white">RentalFlow</span>
        </div>
        <button onclick="closeSidebar()" class="ml-auto text-white/60 hover:text-white"><i class="fas fa-times text-xl"></i></button>
    </div>
    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-0.5">
        <?php foreach($nav as $item): ?>
        <a href="<?php echo $item[0]; ?>" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 <?php echo isActive($requestUri, $item[0]) ? 'bg-white/20 text-white shadow-sm' : 'text-white/70 hover:bg-white/10 hover:text-white'; ?>">
            <i class="fas <?php echo $item[1]; ?> w-5 text-center"></i>
            <span class="flex-1"><?php echo $item[2]; ?></span>
            <?php if ($item[3]): ?><span id="<?php echo $item[3]; ?>Mobile" class="hidden px-2.5 py-1 text-xs font-bold rounded-full bg-amber-400 text-white min-w-[24px] text-center animate-pulse shadow-lg shadow-amber-500/40"></span><?php endif; ?>
        </a>
        <?php endforeach; ?>
    </nav>
    <div class="p-4 border-t border-white/10">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-white/20 text-white flex items-center justify-center font-semibold text-sm"><?php echo $user ? strtoupper(substr($user['name']??'U',0,1).substr(explode(' ', $user['name']??'User')[0]??'U',0,1)) : 'U'; ?></div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate"><?php echo htmlspecialchars($user['name'] ?? 'User'); ?></p>
                <p class="text-xs text-white/60 capitalize"><?php echo $role; ?></p>
            </div>
            <a href="#" onclick="event.preventDefault();logout()" class="text-white/50 hover:text-white transition-colors" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>
</aside>

<!-- Desktop sidebar (inline - pushes content to the right) -->
<aside id="sidebarDesktop" class="hidden lg:flex w-64 flex-shrink-0 bg-gradient-to-b from-blue-700 to-blue-900 shadow-2xl flex-col min-h-screen">
    <div class="h-16 flex items-center px-5 border-b border-white/10">
        <div class="flex items-center gap-3">
            <img src="<?php echo $basePath; ?>/images/rentalflow-logo.png" alt="RentalFlow" class="h-8 w-auto object-contain" onerror="this.style.display='none'">
            <span class="font-bold text-lg text-white">RentalFlow</span>
        </div>
    </div>
    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-0.5">
        <?php foreach($nav as $item): ?>
        <a href="<?php echo $item[0]; ?>" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 <?php echo isActive($requestUri, $item[0]) ? 'bg-white/20 text-white shadow-sm' : 'text-white/70 hover:bg-white/10 hover:text-white'; ?>">
            <i class="fas <?php echo $item[1]; ?> w-5 text-center"></i>
            <span class="flex-1"><?php echo $item[2]; ?></span>
            <?php if ($item[3]): ?><span id="<?php echo $item[3]; ?>Desktop" class="hidden px-2.5 py-1 text-xs font-bold rounded-full bg-amber-400 text-white min-w-[24px] text-center animate-pulse shadow-lg shadow-amber-500/40"></span><?php endif; ?>
        </a>
        <?php endforeach; ?>
    </nav>
    <div class="p-4 border-t border-white/10">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-white/20 text-white flex items-center justify-center font-semibold text-sm"><?php echo $user ? strtoupper(substr($user['name']??'U',0,1).substr(explode(' ', $user['name']??'User')[0]??'U',0,1)) : 'U'; ?></div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate"><?php echo htmlspecialchars($user['name'] ?? 'User'); ?></p>
                <p class="text-xs text-white/60 capitalize"><?php echo $role; ?></p>
            </div>
            <a href="#" onclick="event.preventDefault();logout()" class="text-white/50 hover:text-white transition-colors" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>
</aside>

<?php if(!$nonav): ?>
<div onclick="closeSidebar()" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-30 lg:hidden hidden transition-opacity duration-300" id="sidebarOverlay"></div>
<?php endif; ?>
<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebarMobile');
    const overlay = document.getElementById('sidebarOverlay');
    if (sidebar) { sidebar.classList.toggle('-translate-x-full'); }
    if (overlay) { overlay.classList.toggle('hidden'); document.body.classList.toggle('overflow-hidden'); }
}
function closeSidebar() {
    const sidebar = document.getElementById('sidebarMobile');
    const overlay = document.getElementById('sidebarOverlay');
    if (sidebar) { sidebar.classList.add('-translate-x-full'); }
    if (overlay) { overlay.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); }
}
// Expose globally so other pages can refresh counts after data changes
window.refreshSidebarCounts = loadSidebarCounts;

// Initialize badge counters as soon as the sidebar markup is present
(function initSidebarCounts() {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindSidebarCounts);
    } else {
        bindSidebarCounts();
    }
})();

function bindSidebarCounts() {
    // Wire clicks on sidebar nav links to refresh counts after navigation
    document.querySelectorAll('#sidebarMobile nav a, #sidebarDesktop nav a').forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth < 1024) closeSidebar();
            setTimeout(loadSidebarCounts, 250);
        });
    });
    // Initial load with a small delay so auth/session is fully ready
    setTimeout(loadSidebarCounts, 100);
}

    async function loadSidebarCounts() {
        console.log('Loading sidebar counts...');
        try {
            const token = localStorage.getItem('rf_token') || '<?php echo $token ?? ''; ?>';
            if (!token) {
                console.log('No token found, skipping sidebar counts');
                return;
            }
            
            const userRole = '<?php echo $role; ?>';
            const headers = {'Authorization':'Bearer '+token, 'Content-Type':'application/json'};
            
            // Fetch complaints count with error handling
            let notifyCount = 0;
            try {
                const compRes = await fetch('<?php echo $basePath; ?>/api/complaints', { headers });
                console.log('Complaints response status:', compRes.status);
                if (compRes.ok) {
                    const compData = await compRes.json();
                    const list = compData.complaints || [];
                    if (userRole === 'tenant') {
                        // For tenants: only count unread complaints
                        notifyCount = list.filter(c => c.is_unread && (c.status === 'open' || c.status === 'in-progress')).length;
                    } else {
                        // For owner/caretaker: count all open/in-progress complaints
                        notifyCount = list.filter(c => c.status === 'open' || c.status === 'in-progress').length;
                    }
                }
            } catch(e) {
                console.warn('Failed to load complaints count:', e);
            }
            
            console.log('Notification count:', notifyCount);
            
            ['complaintCountMobile','complaintCountDesktop'].forEach(id => {
                const el = document.getElementById(id);
                if (!el) {
                    console.warn('Element not found:', id);
                    return;
                }
                if (notifyCount > 0) {
                    el.textContent = notifyCount;
                    el.classList.remove('hidden');
                    console.log('Showing badge', id, '=', notifyCount);
                } else {
                    el.classList.add('hidden');
                }
            });
            
            // For tenants, fetch unconfirmed payments count
            if (userRole === 'tenant') {
                let unconfirmed = 0;
                try {
                    const payRes = await fetch('<?php echo $basePath; ?>/api/payments', { headers });
                    if (payRes.ok) {
                        const payData = await payRes.json();
                        unconfirmed = (payData.payments || []).filter(p => {
                            try { return parseInt(p.tenant_confirmed || '0', 10) === 0; } catch(err) { return true; }
                        }).length;
                    }
                } catch(e) {
                    console.warn('Failed to load payments count:', e);
                }
                
                ['paymentCountMobile','paymentCountDesktop'].forEach(id => {
                    const el = document.getElementById(id);
                    if (!el) return;
                    if (unconfirmed > 0) {
                        el.textContent = unconfirmed;
                        el.classList.remove('hidden');
                    } else {
                        el.classList.add('hidden');
                    }
                });
            }
        } catch(e) {
            console.warn('Failed to load sidebar counts', e);
        }
    }
</script>