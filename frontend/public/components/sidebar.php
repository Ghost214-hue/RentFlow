<?php
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$user = $_SESSION['rf_user'] ?? null;
$role = $user['role'] ?? 'owner';

$ownerNav = [
    ['/dashboard','fa-chart-line','Dashboard'],
    ['/properties','fa-building','Properties'],
    ['/houses','fa-home','Houses'],
    ['/tenants','fa-users','Tenants'],
    ['/caretakers','fa-user-shield','Caretakers'],
    ['/payments','fa-money-bill-wave','Payments'],
    ['/bills','fa-file-invoice-dollar','Bills'],
    ['/complaints','fa-exclamation-triangle','Complaints'],
    ['/communications','fa-comments','Messages'],
    ['/documents','fa-file-alt','Rules'],
    ['/reports','fa-chart-pie','Reports'],
];

$caretakerNav = [
    ['/dashboard','fa-chart-line','Dashboard'],
    ['/properties','fa-building','Properties'],
    ['/houses','fa-home','Houses'],
    ['/tenants','fa-users','Tenants'],
    ['/payments','fa-money-bill-wave','Payments'],
    ['/bills','fa-file-invoice-dollar','Bills'],
    ['/complaints','fa-exclamation-triangle','Complaints'],
    ['/documents','fa-file-alt','Rules'],
];

$tenantNav = [
    ['/dashboard','fa-chart-line','Dashboard'],
    ['/tenant-profile','fa-user','My Profile'],
    ['/payments','fa-money-bill-wave','My Payments'],
    ['/bills','fa-file-invoice-dollar','My Bills'],
    ['/complaints','fa-exclamation-triangle','My Complaints'],
    ['/documents','fa-file-alt','Rules'],
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
<aside id="sidebar" class="fixed lg:relative inset-y-0 left-0 z-40 w-64 flex-shrink-0 bg-gradient-to-b from-blue-700 to-blue-900 shadow-2xl transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col h-full min-h-screen">
    <div class="h-16 flex items-center px-5 border-b border-white/10">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-white font-bold text-sm backdrop-blur">RF</div>
            <span class="font-bold text-lg text-white">RentalFlow</span>
        </div>
        <button onclick="closeSidebar()" class="lg:hidden ml-auto text-white/60 hover:text-white"><i class="fas fa-times text-xl"></i></button>
    </div>
    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-0.5">
        <?php foreach($nav as $item): ?>
        <a href="<?php echo $item[0]; ?>" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 <?php echo isActive($requestUri, $item[0]) ? 'bg-white/20 text-white shadow-sm' : 'text-white/70 hover:bg-white/10 hover:text-white'; ?>">
            <i class="fas <?php echo $item[1]; ?> w-5 text-center"></i><?php echo $item[2]; ?>
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
<?php if($requestUri !== '/signin' && $requestUri !== '/signup'): ?>
<div onclick="closeSidebar()" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-30 lg:hidden hidden transition-opacity duration-300" id="sidebarOverlay"></div>
<?php endif; ?>
<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    sidebar.classList.toggle('-translate-x-full');
    if (overlay) {
        overlay.classList.toggle('hidden');
        document.body.classList.toggle('overflow-hidden');
    }
}
function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    sidebar.classList.add('-translate-x-full');
    if (overlay) {
        overlay.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
}
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('#sidebar nav a').forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth < 1024) {
                closeSidebar();
            }
        });
    });
});
</script>