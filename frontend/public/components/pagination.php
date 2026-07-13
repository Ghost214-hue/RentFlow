<?php
// Simple pagination UI component
?>
<div id="__rf_pagination_template" style="display:none">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <nav class="rf-pagination flex items-center justify-center gap-3 text-sm text-slate-700" aria-label="Pagination">
            <button data-action="first" class="px-3 py-1 rounded-lg bg-white border border-slate-200">«</button>
            <button data-action="prev" class="px-3 py-1 rounded-lg bg-white border border-slate-200">‹</button>
            <span data-role="pages" class="px-2"></span>
            <button data-action="next" class="px-3 py-1 rounded-lg bg-white border border-slate-200">›</button>
            <button data-action="last" class="px-3 py-1 rounded-lg bg-white border border-slate-200">»</button>
        </nav>
        <div class="flex items-center gap-2 text-sm text-slate-600">
            <label class="font-medium" for="__rf_per_page_select">Rows:</label>
            <select data-role="per-page-select" class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all text-sm">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
    </div>
</div>

<script>
window.getSavedPerPage = function(storageKey, defaultPerPage = 25) {
    if (!storageKey) return defaultPerPage;
    try {
        const value = localStorage.getItem(storageKey);
        const parsed = Number(value);
        return Number.isInteger(parsed) && parsed > 0 ? parsed : defaultPerPage;
    } catch {
        return defaultPerPage;
    }
};

window.setSavedPerPage = function(storageKey, perPage) {
    if (!storageKey) return;
    try {
        localStorage.setItem(storageKey, String(perPage));
    } catch {
        // ignore storage errors
    }
};

window.renderPagination = function(containerId, meta, onPageChange, options = {}) {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.innerHTML = '';
    const template = document.getElementById('__rf_pagination_template');
    if (!template) return;
    const wrapper = template.firstElementChild.cloneNode(true);

    const pageSpan = wrapper.querySelector('[data-role="pages"]');
    pageSpan.textContent = `Page ${meta.page} of ${meta.total_pages} · ${meta.total} items · ${meta.per_page || ''} per page`;

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
    const perPageKey = options.perPageKey || null;
    const defaultPerPage = options.defaultPerPage || 25;
    if (!perPageKey || !selectEl) {
        if (selectEl) selectEl.closest('div').style.display = 'none';
    } else {
        const savedValue = window.getSavedPerPage(perPageKey, defaultPerPage);
        selectEl.value = String(savedValue);
        selectEl.addEventListener('change', (event) => {
            const newPerPage = Number(event.target.value) || defaultPerPage;
            window.setSavedPerPage(perPageKey, newPerPage);
            if (typeof options.onPerPageChange === 'function') {
                options.onPerPageChange(newPerPage);
            } else {
                onPageChange(1, newPerPage);
            }
        });
    }

    container.appendChild(wrapper);
};
</script>
