<?php
/**
 * Reusable Report Tab Component
 * Displays horizontal tabs for report categories.
 * Usage: report_tab($basePath, $activeTab)
 *
 * @param string $basePath  Base URL path for the application
 * @param string $activeTab The currently active report tab key
 */
function report_tab(string $basePath, string $activeTab = 'tenancy_vacancy'): void
{
    $tabs = [
        'tenancy_vacancy' => [
            'label' => 'Tenancy & Vacancy',
            'icon'  => 'fa-door-open',
            'id'    => 'tab-tenancy-vacancy',
        ],
        'financial' => [
            'label' => 'Financial',
            'icon'  => 'fa-chart-line',
            'id'    => 'tab-financial',
        ],
        'bills' => [
            'label' => 'Bills',
            'icon'  => 'fa-file-invoice-dollar',
            'id'    => 'tab-bills',
        ],
        'complaints' => [
            'label' => 'Complaints',
            'icon'  => 'fa-exclamation-triangle',
            'id'    => 'tab-complaints',
        ],
    ];

    $activeTabKey = in_array($activeTab, array_keys($tabs)) ? $activeTab : 'tenancy_vacancy';
?>
<div class="report-tabs-container mb-6" role="tablist" aria-label="Report categories">
    <nav class="flex flex-wrap gap-1 sm:gap-2 bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm border border-blue-100/50 p-1.5 sm:p-2" aria-label="Report tabs">
        <?php foreach ($tabs as $key => $tab):
            $isActive = $key === $activeTabKey;
        ?>
        <a href="<?php echo htmlspecialchars($basePath); ?>/reports?tab=<?php echo htmlspecialchars($key); ?>"
           id="<?php echo htmlspecialchars($tab['id']); ?>"
           role="tab"
           aria-selected="<?php echo $isActive ? 'true' : 'false'; ?>"
           aria-controls="panel-<?php echo htmlspecialchars($key); ?>"
           tabindex="<?php echo $isActive ? '0' : '-1'; ?>"
           class="report-tab flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:ring-offset-1 <?php
               echo $isActive
                   ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-lg shadow-blue-500/25 active-tab'
                   : 'text-slate-600 hover:bg-blue-50 hover:text-blue-700 hover-tab';
           ?>">
            <i class="fas <?php echo htmlspecialchars($tab['icon']); ?> text-xs sm:text-sm <?php echo $isActive ? 'text-white' : 'text-slate-400'; ?>"></i>
            <span class="whitespace-nowrap"><?php echo htmlspecialchars($tab['label']); ?></span>
            <?php if ($isActive): ?>
            <span class="ml-1 w-1.5 h-1.5 rounded-full bg-white/60 animate-pulse"></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </nav>
</div>

<style>
.report-tabs-container {
    scrollbar-width: none;
    -ms-overflow-style: none;
}
.report-tabs-container::-webkit-scrollbar {
    display: none;
}
.report-tab {
    position: relative;
    overflow: hidden;
}
.report-tab::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 2px;
    background: currentColor;
    transition: width 0.3s ease;
    border-radius: 1px;
    opacity: 0;
}
.report-tab.active-tab::after {
    width: 60%;
    opacity: 0.5;
}
.report-tab.hover-tab:hover::after {
    width: 40%;
    opacity: 0.3;
}
/* Keyboard navigation */
.report-tab:focus-visible {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
    border-radius: 12px;
}
/* Mobile scroll */
@media (max-width: 640px) {
    .report-tabs-container nav {
        overflow-x: auto;
        flex-wrap: nowrap;
        -webkit-overflow-scrolling: touch;
        scroll-snap-type: x mandatory;
    }
    .report-tabs-container nav a {
        scroll-snap-align: start;
        flex-shrink: 0;
    }
}
</style>

<script>
(function() {
    // Enable keyboard navigation for tabs
    const container = document.querySelector('.report-tabs-container nav');
    if (!container) return;

    const tabs = container.querySelectorAll('[role="tab"]');
    tabs.forEach(tab => {
        tab.addEventListener('keydown', function(e) {
            let targetIndex = -1;
            const currentIndex = Array.from(tabs).indexOf(this);

            switch(e.key) {
                case 'ArrowRight':
                    e.preventDefault();
                    targetIndex = (currentIndex + 1) % tabs.length;
                    break;
                case 'ArrowLeft':
                    e.preventDefault();
                    targetIndex = (currentIndex - 1 + tabs.length) % tabs.length;
                    break;
                case 'Home':
                    e.preventDefault();
                    targetIndex = 0;
                    break;
                case 'End':
                    e.preventDefault();
                    targetIndex = tabs.length - 1;
                    break;
            }

            if (targetIndex >= 0 && targetIndex < tabs.length) {
                tabs[targetIndex].focus();
                // Navigate to the tab href
                if (tabs[targetIndex].href) {
                    window.location.href = tabs[targetIndex].href;
                }
            }
        });
    });
})();
</script>
<?php
}