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
require_once __DIR__ . '/../includes/base-path-fix.php';
$basePath = getBasePath();
$_SESSION['rf_user'] = $user;
$role = $user['role'] ?? 'tenant';

// Allow tenant (self), owner, or caretaker to access this page
// If owner/caretaker, require tenant_id query param
$viewTenantId = null;
if ($role === 'tenant') {
    // Tenant viewing their own profile
} elseif ($role === 'owner' || $role === 'caretaker') {
    $viewTenantId = isset($_GET['tenant_id']) ? (int) $_GET['tenant_id'] : null;
    if (!$viewTenantId) {
        header('Location: ' . $basePath . '/tenants');
        exit;
    }
} else {
    header('Location: ../public/signin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - RentaFlow</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($basePath); ?>/css/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-50/30 min-h-screen font-sans text-slate-800 flex flex-col lg:flex-row">
    <?php include __DIR__ . '/../public/components/sidebar.php'; ?>
    <div class="flex-1 flex flex-col min-h-screen">
        <?php include __DIR__ . '/../public/components/header.php'; ?>
        <main class="flex-1 overflow-y-auto p-4 lg:p-8">
            <div class="max-w-3xl mx-auto">
                <h1 class="text-2xl font-bold text-slate-900 mb-6">My Profile</h1>

                <!-- Profile Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6 mb-6">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 text-white flex items-center justify-center text-2xl font-bold overflow-hidden border-2 border-white shadow-sm" id="userInitials">??</div>
                        <div>
                            <p class="font-medium text-slate-900 text-lg" id="userName">-</p>
                            <p class="text-sm text-slate-500" id="userEmail">-</p>
                            <p class="text-xs text-slate-400 mt-0.5" id="userRole">Tenant</p>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Phone Number <span class="text-red-400">*</span></label>
                                <input type="tel" id="userPhone" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="+254 7XX XXX XXX">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                                <input type="email" id="userEmailInput" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="your@email.com">
                            </div>
                        </div>

                        <!-- Profile Picture Upload -->
                        <div class="mt-4 pt-4 border-t border-slate-100" id="profilePicUploadSection">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Profile Picture</label>
                            <div class="flex items-center gap-4">
                                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-50 to-blue-100 text-slate-400 flex items-center justify-center text-2xl font-bold overflow-hidden border-2 border-dashed border-slate-200" id="profilePicPreview">
                                    <span id="profilePicInitials">?</span>
                                </div>
                                <div class="flex-1">
                                    <input type="file" id="profilePicInput" class="hidden" accept=".jpg,.jpeg,.png" onchange="handleProfilePicUpload(this.files)">
                                    <button type="button" onclick="document.getElementById('profilePicInput').click()" class="px-4 py-2 bg-white text-blue-700 border border-blue-200 rounded-xl font-medium hover:bg-blue-50 transition-all inline-flex items-center gap-2">
                                        <i class="fas fa-camera"></i>Choose Photo
                                    </button>
                                    <p class="text-xs text-slate-400 mt-1">JPG or PNG, max 2MB</p>
                                    <div id="profilePicStatus" class="text-xs text-slate-500 mt-1"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button onclick="updateProfile()" class="mt-4 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all">Update Profile</button>
                </div>

                <!-- Next of Kin (Read-only) -->
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6 mb-6">
                    <h3 class="font-semibold text-slate-900 mb-4">Next of Kin</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                            <input type="text" id="kinName" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-100 text-slate-500" disabled>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Phone Number</label>
                            <input type="text" id="kinPhone" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-100 text-slate-500" disabled>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                        <input type="email" id="kinEmail" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-100 text-slate-500" disabled>
                    </div>
                    <p class="text-xs text-slate-400 mt-3"><i class="fas fa-info-circle mr-1"></i>Next of kin information cannot be edited by tenants. Contact your property manager to update these details.</p>
                </div>

                <!-- Lease Info (Read-only) -->
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6 mb-6">
                    <h3 class="font-semibold text-slate-900 mb-4">Lease Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Property</label>
                            <input type="text" id="leaseProperty" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-100 text-slate-500" disabled>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Unit</label>
                            <input type="text" id="userUnit" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-100 text-slate-500" disabled>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Lease Start</label>
                            <input type="text" id="leaseStart" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-100 text-slate-500" disabled>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Lease End</label>
                            <input type="text" id="leaseEnd" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-100 text-slate-500" disabled>
                        </div>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6">
                    <h3 class="font-semibold text-slate-900 mb-4">Change Password</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">New Password</label>
                            <input type="password" id="newPassword" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="Enter new password">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Confirm Password</label>
                            <input type="password" id="confirmPassword" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="Confirm password">
                        </div>
                    </div>
                    <button onclick="updatePassword()" class="mt-4 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all">Update Password</button>
                </div>
            </div>
        </main>
    </div>
    <div id="toast" class="fixed bottom-6 right-6 z-50 hidden px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2"></div>
    <script>
    // Calculate base path - navigate up from /frontend/pages/ to project root
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
        const res = await fetch(url, { ...options, headers });
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); } catch(e) { throw new Error('Server error'); }
        
        if (!res.ok) {
            if (res.status === 401) {
                localStorage.removeItem('rf_token');
                window.location.href = `${BASE}/signin`;
            }
            throw new Error(data.error || 'Request failed');
        }
        return data;
    }

    function toast(msg, type='success') {
        const el = document.getElementById('toast');
        if (!el) return;
        const colors = { success:'bg-gradient-to-r from-emerald-500 to-emerald-600', error:'bg-gradient-to-r from-red-500 to-red-600', info:'bg-gradient-to-r from-blue-500 to-blue-600' };
        const icons = { success:'fa-check-circle', error:'fa-exclamation-circle', info:'fa-info-circle' };
        el.className = `fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2 ${colors[type]||colors.success}`;
        el.innerHTML = `<i class="fas ${icons[type]||icons.success}"></i>${msg}`;
        el.classList.remove('hidden');
        setTimeout(() => el.classList.add('hidden'), 3000);
    }

    function initials(n) { return n.split(' ').map(s=>s[0]).join('').substring(0,2).toUpperCase(); }

    function normalizeUrl(url) {
        if (!url) return '';
        if (url.startsWith('http://') || url.startsWith('https://')) return url;
        const base = String(BASE || '').trim();
        const path = String(url).trim();
        
        // If path already starts with base path, don't duplicate it
        if (base && path.startsWith(base + '/')) {
            return path;
        }
        
        // Otherwise prepend base path
        if (path.startsWith('/')) return base + path;
        return base + '/' + path;
    }

    async function uploadFile(file) {
        const formData = new FormData();
        formData.append('file', file);
        const authHeader = headers.Authorization ? { Authorization: headers.Authorization } : {};
        const res = await fetch(`${API}/upload`, {
            method: 'POST',
            body: formData,
            headers: authHeader
        });
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); } catch(e) { data = {}; }
        if (!res.ok) throw new Error(data.error || 'Upload failed');
        return data;
    }

    async function handleProfilePicUpload(files) {
        const status = document.getElementById('profilePicStatus');
        const preview = document.getElementById('profilePicPreview');
        const initialsEl = document.getElementById('profilePicInitials');
        if (!files || !files[0]) return;

        try {
            status.textContent = 'Uploading...';
            const result = await uploadFile(files[0]);
            const fileData = result.file;
            
            // Update preview
            if (preview) preview.innerHTML = `<img src="${fileData.url}" class="w-full h-full object-cover" alt="Profile">`;
            if (preview) {
                preview.classList.remove('bg-gradient-to-br', 'from-blue-50', 'to-blue-100', 'text-slate-400');
                preview.classList.add('border-2', 'border-blue-200');
            }
            if (initialsEl) initialsEl.textContent = '';

            // Store for saving
            window.tempProfilePic = fileData;
            status.textContent = 'Selected: ' + fileData.name;
            toast('Profile picture ready to save', 'success');
        } catch(err) {
            status.textContent = '';
            toast(err.message, 'error');
        }
    }

    function setProfilePicture(url) {
        // Update the main avatar in the profile header
        const avatar = document.getElementById('userInitials');
        const preview = document.getElementById('profilePicPreview');
        const initialsEl = document.getElementById('profilePicInitials');

        if (!avatar) return;

        if (!url) {
            // No picture: show initials in both avatar and preview
            const fallbackText = '??';
            avatar.textContent = fallbackText;
            avatar.className = 'w-20 h-20 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 text-white flex items-center justify-center text-2xl font-bold overflow-hidden border-2 border-white shadow-sm';

            if (preview && initialsEl) {
                preview.innerHTML = `<span id="profilePicInitials">${fallbackText}</span>`;
                preview.className = 'w-20 h-20 rounded-full bg-gradient-to-br from-blue-50 to-blue-100 text-slate-400 flex items-center justify-center text-2xl font-bold overflow-hidden border-2 border-dashed border-slate-200';
                initialsEl.textContent = fallbackText;
            }
            return;
        }

        // Picture exists: show image in both avatar and preview
        avatar.innerHTML = `<img src="${url}" class="w-full h-full object-cover" alt="Profile">`;
        avatar.className = 'w-20 h-20 rounded-full overflow-hidden border-2 border-white shadow-sm';

        if (preview) {
            preview.innerHTML = `<img src="${url}" class="w-full h-full object-cover" alt="Profile">`;
            preview.className = 'w-20 h-20 rounded-full overflow-hidden border-2 border-blue-200';
            if (initialsEl) initialsEl.textContent = '';
        }
    }

    async function loadProfile() {
        try {
            const profileData = await apiRequest(`${API}/auth/me`);
            if (profileData.user) {
                const userNameEl = document.getElementById('userName');
                const userEmailEl = document.getElementById('userEmail');
                if (userNameEl) userNameEl.textContent = profileData.user.name;
                if (userEmailEl) userEmailEl.value = profileData.user.email || '';
                
                // Note: initials are now set in the tenant data section below,
                // after checking for profile_picture to avoid overwriting avatars
            }

            // Determine which tenant to load: for owner/caretaker use viewTenantId, else self
            <?php if ($viewTenantId): ?>
            const tenantData = await apiRequest(`${API}/tenants/<?php echo (int) $viewTenantId; ?>`);
            const me = tenantData.tenant || tenantData;
            <?php else: ?>
            const tenantData = await apiRequest(`${API}/tenants`);
            const me = (tenantData.tenants && tenantData.tenants.length > 0) ? tenantData.tenants[0] : null;
            <?php endif; ?>

            if (me) {
                const userPhoneEl = document.getElementById('userPhone');
                const userUnitEl = document.getElementById('userUnit');
                const leasePropertyEl = document.getElementById('leaseProperty');
                const leaseStartEl = document.getElementById('leaseStart');
                const leaseEndEl = document.getElementById('leaseEnd');
                const kinNameEl = document.getElementById('kinName');
                const kinPhoneEl = document.getElementById('kinPhone');
                const kinEmailEl = document.getElementById('kinEmail');
                
                if (userPhoneEl) userPhoneEl.value = me.phone || '';
                if (userUnitEl) userUnitEl.value = me.house_unit || '-';
                if (leasePropertyEl) leasePropertyEl.value = me.property_name || '-';
                if (leaseStartEl) leaseStartEl.value = me.lease_start ? new Date(me.lease_start).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'}) : 'N/A';
                if (leaseEndEl) leaseEndEl.value = me.lease_end ? new Date(me.lease_end).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'}) : 'N/A';
                
                // Next of Kin (read-only)
                if (kinNameEl) kinNameEl.value = me.next_of_kin_name || '-';
                if (kinPhoneEl) kinPhoneEl.value = me.next_of_kin_phone || '-';
                if (kinEmailEl) kinEmailEl.value = me.next_of_kin_email || '-';

                // Sync profile picture between header avatar and upload preview
                // IMPORTANT: Do this BEFORE setting initials, so profile picture takes precedence
                const userInitials = document.getElementById('userInitials');
                const profilePicInitials = document.getElementById('profilePicInitials');
                
                if (me.profile_picture) {
                    // Normalize the URL to include base path
                    const normalizedPicUrl = normalizeUrl(me.profile_picture);
                    setProfilePicture(normalizedPicUrl);
                } else {
                    // Only show initials if there's no profile picture
                    const name = profileData.user?.name || me.name || 'User';
                    const initialsText = initials(name);
                    if (userInitials) userInitials.textContent = initialsText;
                    if (profilePicInitials) profilePicInitials.textContent = initialsText;
                }
            }
        } catch(e) {
            console.error(e);
            if (e.message.includes('401')) window.location.href = `${BASE}/signin`;
        }
    }

    async function updateProfile() {
        const phoneEl = document.getElementById('userPhone');
        const emailEl = document.getElementById('userEmailInput');
        const phone = phoneEl ? phoneEl.value.trim() : '';
        const email = emailEl ? emailEl.value.trim() : '';
        
        if (!phone) {
            toast('Phone number is required', 'error');
            return;
        }

        let sanitizedEmail = null;
        if (email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                toast('Invalid email format', 'error');
                return;
            }
            sanitizedEmail = email.toLowerCase();
        }

        try {
            // Determine tenant ID
            <?php if ($viewTenantId): ?>
            const tenantId = <?php echo (int) $viewTenantId; ?>;
            const updateData = {
                phone: phone,
                email: sanitizedEmail
            };
            <?php else: ?>
            const tenantData = await apiRequest(`${API}/tenants`);
            if (!tenantData.tenants || tenantData.tenants.length === 0) {
                toast('Tenant record not found', 'error');
                return;
            }
            const tenantId = tenantData.tenants[0].id;
            const updateData = {
                phone: phone,
                email: sanitizedEmail
            };
            <?php endif; ?>

            // Include profile picture if uploaded
            if (window.tempProfilePic && window.tempProfilePic.url) {
                updateData.profile_picture = window.tempProfilePic.url;
            }

            const result = await apiRequest(`${API}/tenants/${tenantId}`, {
                method: 'PUT',
                body: JSON.stringify(updateData)
            });
            
            toast('Profile updated successfully!', 'success');
            window.tempProfilePic = null;
            const profilePicStatusEl = document.getElementById('profilePicStatus');
            if (profilePicStatusEl) profilePicStatusEl.textContent = '';
            loadProfile(); // Reload to show updated data
        } catch(e) {
            console.error(e);
            toast(e.message || 'Failed to update profile', 'error');
        }
    }

    async function updatePassword() {
        const newPasswordEl = document.getElementById('newPassword');
        const confirmPasswordEl = document.getElementById('confirmPassword');
        const newPassword = newPasswordEl ? newPasswordEl.value : '';
        const confirmPassword = confirmPasswordEl ? confirmPasswordEl.value : '';
        
        if (!newPassword || newPassword.length < 6) {
            toast('Password must be at least 6 characters', 'error');
            return;
        }
        
        if (newPassword !== confirmPassword) {
            toast('Passwords do not match', 'error');
            return;
        }

        try {
            const result = await apiRequest(`${API}/auth/change-password`, {
                method: 'POST',
                body: JSON.stringify({
                    new_password: newPassword
                })
            });
            
            toast('Password updated successfully!', 'success');
            if (newPasswordEl) newPasswordEl.value = '';
            if (confirmPasswordEl) confirmPasswordEl.value = '';
        } catch(e) {
            console.error(e);
            toast(e.message || 'Failed to update password', 'error');
        }
    }

    loadProfile();
    </script>
</body>
</html>