<?php
require_once __DIR__ . '/../includes/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Documents - RentFlow</title>
    <link rel="stylesheet" href="/css/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-50/30 min-h-screen font-sans text-slate-800 flex flex-col lg:flex-row">
    <?php include __DIR__ . '/../public/components/sidebar.php'; ?>
    <div class="flex-1 flex flex-col min-h-screen">
        <?php include __DIR__ . '/../public/components/header.php'; ?>
        <main class="flex-1 overflow-y-auto p-4 lg:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Property Documents</h1>
                    <p class="text-slate-500 mt-1">Manage rules, regulations, and policies</p>
                </div>
                <?php if ($role === 'owner'): ?>
                <button onclick="openDocumentModal()" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all inline-flex items-center gap-2">
                    <i class="fas fa-plus"></i>Add Document
                </button>
                <?php endif; ?>
            </div>

            <!-- Documents Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="documentsGrid">
                <div class="col-span-full py-12 text-center text-slate-400">Loading documents...</div>
            </div>
        </main>
    </div>

    <!-- Document Modal (Owner Only) -->
    <div id="documentModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" onclick="if(event.target===this)closeDocumentModal()">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto p-6" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4">
                <h3 id="documentModalTitle" class="text-xl font-bold text-slate-900">Add Document</h3>
                <button onclick="closeDocumentModal()" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-xl"></i></button>
            </div>
            
            <form id="documentForm" class="space-y-4">
                <input type="hidden" id="documentId">
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Title *</label>
                    <input type="text" id="docTitle" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="e.g., House Rules & Regulations" required>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Type</label>
                        <select id="docType" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all">
                            <option value="rules">Rules</option>
                            <option value="regulations">Regulations</option>
                            <option value="policy">Policy</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Version</label>
                        <input type="text" id="docVersion" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="1.0" value="1.0">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Apply to Property (Optional)</label>
                    <select id="docProperty" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all">
                        <option value="">All Properties (Global Rules)</option>
                    </select>
                    <p class="text-xs text-slate-500 mt-1">Leave empty to apply to all properties, or select a specific property</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Content *</label>
                    <textarea id="docContent" rows="12" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all font-mono text-sm" placeholder="Enter the document content here...&#10;&#10;Use line breaks for paragraphs." required></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="docActive" checked class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                    <label for="docActive" class="text-sm font-medium text-slate-700">Active (visible to tenants and caretakers)</label>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                    <button type="button" onclick="closeDocumentModal()" class="px-5 py-2.5 bg-white text-slate-700 border border-slate-200 rounded-xl font-medium hover:bg-slate-50 transition-all">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 transition-all">Save Document</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Document Modal -->
    <div id="viewDocumentModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" onclick="if(event.target===this)closeViewDocumentModal()">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto p-6" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 id="viewDocTitle" class="text-xl font-bold text-slate-900">Document Title</h3>
                    <p id="viewDocMeta" class="text-sm text-slate-500 mt-1"></p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="downloadPdf()" class="px-4 py-2 bg-red-600 text-white rounded-xl font-medium hover:bg-red-700 transition-all inline-flex items-center gap-2">
                        <i class="fas fa-file-pdf"></i>Download PDF
                    </button>
                    <button onclick="closeViewDocumentModal()" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-xl"></i></button>
                </div>
            </div>
            
            <div class="bg-slate-50 rounded-xl p-6 border border-slate-200">
                <div id="viewDocContent" class="document-content max-w-none"></div>
            </div>
        </div>
    </div>

    <div id="toast" class="fixed bottom-6 right-6 z-50 hidden px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2"></div>

    <style>
        .document-content { line-height: 1.8; color: #334155; }
        .document-content p { margin-bottom: 1em; }
        .document-content h1, .document-content h2, .document-content h3 { color: #1e293b; margin-top: 1.5em; margin-bottom: 0.75em; font-weight: 700; }
        .document-content h1 { font-size: 1.5em; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5em; }
        .document-content h2 { font-size: 1.3em; color: #2563eb; }
        .document-content h3 { font-size: 1.1em; color: #475569; }
        .document-content ul { margin: 1em 0; padding-left: 1.5em; }
        .document-content li { margin-bottom: 0.5em; }
        .consequences-section { background: #fef2f2; border-left: 4px solid #dc2626; padding: 1em 1.25em; margin: 1.25em 0; border-radius: 0 8px 8px 0; }
        .consequences-section h4 { color: #dc2626; margin: 0 0 0.75em 0; font-size: 1em; text-transform: uppercase; letter-spacing: 0.05em; }
        .consequences-section ul { margin: 0; }
        .consequences-section li { color: #7f1d1d; }
    </style>
    <script>
    const API = '/api';
    const token = localStorage.getItem('rf_token') || '<?php echo $token; ?>';
    const userRole = '<?php echo $role; ?>';
    let currentDocumentId = null;

    function getHeaders(isGetRequest = false) {
        const headers = {};
        if (token) {
            headers['Authorization'] = 'Bearer ' + token;
        }
        // Only add Content-Type for non-GET requests
        if (!isGetRequest) {
            headers['Content-Type'] = 'application/json';
        }
        return headers;
    }

    function toast(msg, type='success') {
        const el = document.getElementById('toast');
        const colors = { success:'bg-gradient-to-r from-emerald-500 to-emerald-600', error:'bg-gradient-to-r from-red-500 to-red-600', info:'bg-gradient-to-r from-blue-500 to-blue-600' };
        const icons = { success:'fa-check-circle', error:'fa-exclamation-circle', info:'fa-info-circle' };
        el.className = `fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2 ${colors[type]||colors.success}`;
        el.innerHTML = `<i class="fas ${icons[type]||icons.success}"></i>${msg}`;
        el.classList.remove('hidden');
        setTimeout(() => el.classList.add('hidden'), 3000);
    }

    async function loadProperties() {
        try {
            const res = await fetch(`${API}/properties`, { headers: getHeaders(true) });
            const data = await res.json();
            const select = document.getElementById('docProperty');
            
            if (data.properties && data.properties.length) {
                data.properties.forEach(prop => {
                    const option = document.createElement('option');
                    option.value = prop.id;
                    option.textContent = prop.name || `Property ${prop.id}`;
                    select.appendChild(option);
                });
            }
        } catch(e) {
            console.error('Failed to load properties:', e);
        }
    }

    async function loadDocuments() {
        try {
            const res = await fetch(`${API}/documents`, { headers: getHeaders(true) });
            const data = await res.json();
            const grid = document.getElementById('documentsGrid');
            
            if (data.documents && data.documents.length) {
                grid.innerHTML = data.documents.map(doc => `
                    <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center">
                                <i class="fas fa-file-alt text-xl"></i>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-medium ${getTypeColor(doc.type)}">${doc.type}</span>
                        </div>
                        
                        <h3 class="text-lg font-bold text-slate-900 mb-2">${escapeHtml(doc.title)}</h3>
                        <p class="text-sm text-slate-500 mb-4">Version ${doc.version}</p>
                        
                        <div class="flex items-center justify-between pt-4 border-t border-blue-50">
                            <button onclick="viewDocument(${doc.id})" class="px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-xl transition-colors">
                                <i class="fas fa-eye mr-1"></i>View
                            </button>
                            <button onclick="downloadPdf(${doc.id})" class="px-4 py-2 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-xl transition-colors">
                                <i class="fas fa-download mr-1"></i>PDF
                            </button>
                        </div>
                        
                        ${userRole === 'owner' ? `
                        <div class="flex items-center gap-2 mt-3 pt-3 border-t border-blue-50">
                            <button onclick="editDocument(${doc.id})" class="flex-1 px-3 py-1.5 text-xs font-medium text-slate-600 bg-slate-50 hover:bg-slate-100 rounded-lg transition-colors">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                            <button onclick="deleteDocument(${doc.id})" class="px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        ` : ''}
                    </div>
                `).join('');
            } else {
                grid.innerHTML = '<div class="col-span-full py-12 text-center text-slate-400">No documents found</div>';
            }
        } catch(e) {
            console.error('Failed to load documents:', e);
            if (e.message.includes('401')) window.location.href = '/signin';
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

    function openDocumentModal() {
        document.getElementById('documentModalTitle').textContent = 'Add Document';
        document.getElementById('documentId').value = '';
        document.getElementById('documentForm').reset();
        document.getElementById('docActive').checked = true;
        document.getElementById('documentModal').classList.remove('hidden');
    }

    function closeDocumentModal() {
        document.getElementById('documentModal').classList.add('hidden');
    }

    async function editDocument(id) {
        try {
            const res = await fetch(`${API}/documents/${id}`, { headers: getHeaders(true) });
            const data = await res.json();
            
            if (!res.ok) throw new Error(data.error || 'Failed to load document');
            
            const doc = data.document;
            document.getElementById('documentModalTitle').textContent = 'Edit Document';
            document.getElementById('documentId').value = doc.id;
            document.getElementById('docTitle').value = doc.title;
            document.getElementById('docType').value = doc.type;
            document.getElementById('docVersion').value = doc.version;
            document.getElementById('docContent').value = doc.content;
            document.getElementById('docActive').checked = doc.is_active;
            
            document.getElementById('documentModal').classList.remove('hidden');
        } catch(e) {
            toast(e.message, 'error');
        }
    }

    async function deleteDocument(id) {
        if (!confirm('Are you sure you want to delete this document?')) return;
        
        try {
            const res = await fetch(`${API}/documents/${id}`, {
                method: 'DELETE',
                headers: getHeaders()
            });
            const data = await res.json();
            
            if (!res.ok) throw new Error(data.error || 'Failed to delete');
            
            toast('Document deleted');
            loadDocuments();
        } catch(e) {
            toast(e.message, 'error');
        }
    }

    async function viewDocument(id) {
        try {
            const res = await fetch(`${API}/documents/${id}`, { headers: getHeaders(true) });
            const data = await res.json();
            
            if (!res.ok) throw new Error(data.error || 'Failed to load document');
            
            const doc = data.document;
            currentDocumentId = id;
            
            document.getElementById('viewDocTitle').textContent = doc.title;
            document.getElementById('viewDocMeta').textContent = `${doc.property_name || 'All Properties'} • ${doc.type} • Version ${doc.version} • Published: ${doc.published_at ? new Date(doc.published_at).toLocaleDateString('en-GB') : 'N/A'}`;
            document.getElementById('viewDocContent').innerHTML = formatDocumentContent(doc.content);
            
            document.getElementById('viewDocumentModal').classList.remove('hidden');
        } catch(e) {
            toast(e.message, 'error');
        }
    }

    function closeViewDocumentModal() {
        document.getElementById('viewDocumentModal').classList.add('hidden');
        currentDocumentId = null;
    }

    function formatDocumentContent(content) {
        if (!content) return '';
        
        // Decode HTML entities first, then re-escape for safe HTML insertion
        const decoded = content
            .replace(/&/g, '&')
            .replace(/</g, '<')
            .replace(/>/g, '>');
        
        let html = decoded
            .replace(/&/g, '&')
            .replace(/</g, '<')
            .replace(/>/g, '>')
            .replace(/CONSEQUENCES:/gi, '<div class="consequences-section"><h4>Consequences</h4><ul>')
            .replace(/CONSEQUENCES OF BREACH:/gi, '<div class="consequences-section"><h4>Consequences of Breach</h4><ul>')
            .replace(/PENALTIES:/gi, '<div class="consequences-section"><h4>Penalties</h4><ul>')
            .split('\n')
            .map(line => {
                line = line.trim();
                if (!line) return '';
                
                // Bullet points
                if (/^[-•*]\s+/.test(line)) {
                    const text = line.replace(/^[-•*]\s+/, '');
                    return `<li>${text}</li>`;
                }
                
                // Numbered items
                if (/^\d+\.\s+/.test(line)) {
                    return `<li>${line.replace(/^\d+\.\s+/, '')}</li>`;
                }
                
                // Headers
                if (/^#{1,3}\s+/.test(line)) {
                    const level = line.match(/^(#{1,3})/)[1].length;
                    const text = line.replace(/^#{1,3}\s+/, '');
                    return `<h${level}>${text}</h${level}>`;
                }
                
                // Closing consequences section
                if (line.match(/^---+\s*$/)) {
                    return '</ul></div>';
                }
                
                // Paragraph
                if (line.length > 0) {
                    return `<p>${line}</p>`;
                }
                
                return '';
            })
            .join('');
        
        // Close any unclosed consequences sections
        if (html.includes('consequences-section') && !html.includes('</ul></div>')) {
            html += '</ul></div>';
        }
        
        return html;
    }

    async function downloadPdf(id) {
        const docId = id || currentDocumentId;
        if (!docId) return;
        
        try {
            toast('Preparing PDF download...', 'info');
            
            // First get the document content
            const docRes = await fetch(`${API}/documents/${docId}`, { headers: getHeaders(true) });
            if (!docRes.ok) {
                throw new Error('Failed to load document for PDF generation');
            }
            const docData = await docRes.json();
            const document = docData.document;
            
            // Generate PDF using browser print functionality as fallback
            const printWindow = window.open('', '_blank');
            if (!printWindow) {
                throw new Error('Please allow popups for PDF download');
            }
            
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>${escapeHtml(document.title)}</title>
                    <style>
                        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 40px; color: #333; }
                        .header { text-align: center; border-bottom: 3px solid #2563eb; padding-bottom: 20px; margin-bottom: 30px; }
                        .header h1 { color: #2563eb; margin: 0; font-size: 28px; }
                        .property-name { font-size: 16px; font-weight: 600; color: #1e40af; margin-bottom: 10px; }
                        .meta { color: #666; margin-top: 10px; font-size: 14px; }
                        .content { line-height: 1.8; font-size: 14px; white-space: pre-wrap; margin-top: 30px; }
                        .footer { margin-top: 50px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #666; font-size: 12px; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <div class="property-name">${document.property_name || 'All Properties'}</div>
                        <h1>${escapeHtml(document.title)}</h1>
                        <div class="meta">
                            <span>${document.type.toUpperCase()}</span> | 
                            <span>Version ${document.version}</span> | 
                            <span>${document.published_at ? new Date(document.published_at).toLocaleDateString('en-GB') : 'N/A'}</span>
                        </div>
                    </div>
                    <div class="content">${formatDocumentContent(document.content)}</div>
                    <div class="footer">
                        <p>RentFlow Property Management System</p>
                        <p>Generated on ${new Date().toLocaleDateString('en-GB')}</p>
                    </div>
                </body>
                </html>
            `);
            printWindow.document.close();
            
            // Wait for content to load then trigger print dialog
            setTimeout(() => {
                printWindow.print();
                toast('Use the print dialog to save as PDF', 'info');
            }, 500);
            
        } catch(e) {
            console.error('PDF download failed:', e);
            toast(e.message || 'Failed to download PDF. Please try again.', 'error');
        }
    }

    document.getElementById('documentForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const id = document.getElementById('documentId').value;
        const propertyId = document.getElementById('docProperty').value;
        const data = {
            title: document.getElementById('docTitle').value,
            type: document.getElementById('docType').value,
            version: document.getElementById('docVersion').value,
            content: document.getElementById('docContent').value,
            is_active: document.getElementById('docActive').checked,
            property_id: propertyId ? parseInt(propertyId) : null
        };
        
        try {
            const method = id ? 'PUT' : 'POST';
            const url = id ? `${API}/documents/${id}` : `${API}/documents`;
            
            const res = await fetch(url, {
                method,
                headers: getHeaders(),
                body: JSON.stringify(data)
            });
            
            const result = await res.json();
            if (!res.ok) throw new Error(result.error || 'Failed to save');
            
            toast(id ? 'Document updated' : 'Document created');
            closeDocumentModal();
            loadDocuments();
        } catch(err) {
            toast(err.message, 'error');
        }
    });

    loadProperties();
    loadDocuments();
    </script>
</body>
</html>