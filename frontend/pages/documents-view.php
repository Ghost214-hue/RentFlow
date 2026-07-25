<?php
require_once __DIR__ . '/../includes/base-path-fix.php';
$basePath = getBasePath();
require_once __DIR__ . '/../includes/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Documents - RentaFlow</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($basePath); ?>/css/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-50/30 min-h-screen font-sans text-slate-800 flex flex-col lg:flex-row">
    <?php include __DIR__ . '/../public/components/sidebar.php'; ?>
    <div class="flex-1 flex flex-col min-h-screen">
        <?php include __DIR__ . '/../public/components/header.php'; ?>
        <main class="flex-1 overflow-y-auto p-4 lg:p-8">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-slate-900">Property Documents</h1>
                <p class="text-slate-500 mt-1">View rules, regulations, and policies</p>
            </div>

            <!-- Documents Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="documentsGrid">
                <div class="col-span-full py-12 text-center text-slate-400">Loading documents...</div>
            </div>
        </main>
    </div>

    <!-- View Document Modal -->
    <div id="viewDocumentModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" onclick="if(event.target===this)closeViewDocumentModal()">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto p-6" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 id="viewDocTitle" class="text-xl font-bold text-slate-900">Document Title</h3>
                    <p id="viewDocMeta" class="text-sm text-slate-500 mt-1"></p>
                </div>
                <div id="paymentBadge" class="hidden px-3 py-2 rounded-xl text-sm font-medium bg-emerald-50 text-emerald-700 border border-emerald-100"></div>
                <div class="flex items-center gap-2">
                    <button onclick="downloadPdf()" class="px-4 py-2 bg-red-600 text-white rounded-xl font-medium hover:bg-red-700 transition-all inline-flex items-center gap-2">
                        <i class="fas fa-file-pdf"></i>Download PDF
                    </button>
                    <button onclick="shareDocument()" class="px-4 py-2 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700 transition-all inline-flex items-center gap-2">
                        <i class="fas fa-share-alt"></i>Share
                    </button>
                    <button onclick="closeViewDocumentModal()" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-xl"></i></button>
                </div>
            </div>
            
            <div class="bg-slate-50 rounded-xl p-6 border border-slate-200">
                <div id="viewDocContent" class="prose prose-slate max-w-none whitespace-pre-wrap"></div>
            </div>
        </div>
    </div>

    <div id="toast" class="fixed bottom-6 right-6 z-50 hidden px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2"></div>

    <script>
    const API = '/api';
    const token = localStorage.getItem('rf_token') || '<?php echo $token; ?>';
    const headers = token ? {'Authorization':'Bearer '+token, 'Content-Type':'application/json'} : {'Content-Type':'application/json'};
    let currentDocumentId = null;

    function toast(msg, type='success') {
        const el = document.getElementById('toast');
        const colors = { success:'bg-gradient-to-r from-emerald-500 to-emerald-600', error:'bg-gradient-to-r from-red-500 to-red-600', info:'bg-gradient-to-r from-blue-500 to-blue-600' };
        const icons = { success:'fa-check-circle', error:'fa-exclamation-circle', info:'fa-info-circle' };
        el.className = `fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2 ${colors[type]||colors.success}`;
        el.innerHTML = `<i class="fas ${icons[type]||icons.success}"></i>${msg}`;
        el.classList.remove('hidden');
        setTimeout(() => el.classList.add('hidden'), 3000);
    }

    async function loadDocuments() {
        try {
            const res = await fetch(`${API}/documents`, { headers });
            const data = await res.json();
            const grid = document.getElementById('documentsGrid');
            
            if (data.documents && data.documents.length) {
                grid.innerHTML = data.documents.map(doc => `
                    <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6 hover:shadow-md transition-shadow cursor-pointer" onclick="viewDocument(${doc.id})">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center">
                                <i class="fas fa-file-alt text-xl"></i>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-medium ${getTypeColor(doc.type)}">${doc.type}</span>
                        </div>
                        
                        <h3 class="text-lg font-bold text-slate-900 mb-2">${escapeHtml(doc.title)}</h3>
                        <p class="text-sm text-slate-500 mb-4">Version ${doc.version}</p>
                        
                        <div class="flex items-center justify-between pt-4 border-t border-blue-50">
                            <button onclick="event.stopPropagation(); viewDocument(${doc.id})" class="px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-xl transition-colors">
                                <i class="fas fa-eye mr-1"></i>View
                            </button>
                            <button onclick="event.stopPropagation(); downloadPdf(${doc.id})" class="px-4 py-2 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-xl transition-colors">
                                <i class="fas fa-download mr-1"></i>PDF
                            </button>
                        </div>
                    </div>
                `).join('');
            } else {
                grid.innerHTML = '<div class="col-span-full py-12 text-center text-slate-400">No documents available</div>';
            }
        } catch(e) {
            console.error('Failed to load documents:', e);
            if (e.message.includes('401')) window.location.href = BASE + '/signin';
        }
    }

    function getTypeColor(type) {
        const colors = {
            'rules': 'bg-blue-100 text-blue-700',
            'regulations': 'bg-purple-100 text-purple-700',
            'policy': 'bg-amber-100 text-amber-700',
            'other': 'bg-slate-100 text-slate-700'
        };
        return colors[type] || colors['other'];
    }

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value;
        return div.innerHTML;
    }

    async function viewDocument(id) {
        try {
            const res = await fetch(`${API}/documents/${id}`, { headers });
            const data = await res.json();

            if (!res.ok) throw new Error(data.error || 'Failed to load document');

            const doc = data.document;
            currentDocumentId = id;

            document.getElementById('viewDocTitle').textContent = doc.title;
            document.getElementById('viewDocMeta').textContent = `${doc.type} • Version ${doc.version} • Published: ${doc.published_at ? new Date(doc.published_at).toLocaleDateString('en-GB') : 'N/A'}`;
            document.getElementById('viewDocContent').textContent = doc.content;

            const badge = document.getElementById('paymentBadge');
            if (doc.property && doc.property.payment_method_type) {
                let text = '';
                const p = doc.property;
                switch (p.payment_method_type) {
                    case 'paybill':
                        text += `Paybill: ${p.paybill_number || ''}` + (p.paybill_account ? ` (${p.paybill_account})` : '');
                        break;
                    case 'till':
                        text += `Till: ${p.till_number || ''}`;
                        break;
                    case 'bank':
                        text += `${p.bank_name || 'Bank'}${p.bank_branch ? ' - ' + p.bank_branch : ''}${p.bank_account ? ' • Acc: ' + p.bank_account : ''}`;
                        break;
                    case 'mobile_money':
                        text += `M-Pesa: ${p.mobile_money_number || ''}`;
                        break;
                    default:
                        text = '';
                }
                if (text) {
                    badge.textContent = text;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            } else {
                badge.classList.add('hidden');
            }

            document.getElementById('viewDocumentModal').classList.remove('hidden');
        } catch(e) {
            toast(e.message, 'error');
        }
    }

    function closeViewDocumentModal() {
        document.getElementById('viewDocumentModal').classList.add('hidden');
        currentDocumentId = null;
    }

    function downloadPdf(id) {
        const docId = id || currentDocumentId;
        if (!docId) return;
        
        window.open(`${API}/documents/${docId}/pdf`, '_blank');
    }

    function shareDocument() {
        const docId = currentDocumentId;
        if (!docId) return;
        
        // Copy link to clipboard
        const link = `${window.location.origin}/api/documents/${docId}/pdf`;
        navigator.clipboard.writeText(link).then(() => {
            toast('Document link copied to clipboard!', 'success');
        }).catch(() => {
            toast('Failed to copy link', 'error');
        });
    }

    loadDocuments();
    </script>
</body>
</html>