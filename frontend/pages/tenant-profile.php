<?php
session_start();
$token = $_COOKIE['rf_token'] ?? $_SESSION['rf_token'] ?? null;
if (!$token) { header('Location: ../public/signin.php'); exit; }
require_once __DIR__ . '/../../backend/app/Core/Env.php';
\App\Core\Env::load();
require_once __DIR__ . '/../../backend/app/Core/JWT.php';
$jwt = new \App\Core\JWT();
$user = $jwt->decode($token);
if (!$user) { header('Location: ../public/signin.php'); exit; }
require_once __DIR__ . '/../includes/base-path-fix.php';
$basePath = getBasePath();
$_SESSION['rf_user'] = $user;
$role = $user['role'] ?? 'tenant';
if ($role !== 'tenant') { header('Location: ../public/signin.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - RentaFlow</title>
    <link rel="stylesheet" href="<?php echo $basePath; ?>/css/output.css">
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
                        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 text-white flex items-center justify-center text-2xl font-bold" id="userInitials">??</div>
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
        // Fallback: remove last path segment
        BASE = BASE.replace(/\/[^\/]*$/, '');
    }
    const API = BASE + '/api';
    const token = localStorage.getItem('rf_token') || '<?php echo $token; ?>';
    const headers = token ? {'Authorization':'Bearer '+token, 'Content-Type':'application/json'} : {'Content-Type':'application/json'};

    async function apiRequest(url, options = {}) {
        const res = await fetch(url, { ...options, headers });
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); } catch(e) { throw new Error('Server error'); }
        
        if (!res.ok) {
            if (res.status === 401) {
                localStorage.removeItem('rf_token');
                window.location.href = 'signin';
            }
            throw new Error(data.error || 'Request failed');
        }
        return data;
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

    function initials(n) { return n.split(' ').map(s=>s[0]).join('').substring(0,2).toUpperCase(); }

    async function loadProfile() {
        try {
            // Get profile from JWT
            const profileData = await apiRequest(`${API}/auth/me`);
            if (profileData.user) {
                document.getElementById('userInitials').textContent = initials(profileData.user.name);
                document.getElementById('userName').textContent = profileData.user.name;
                document.getElementById('userEmail').value = profileData.user.email || '';
            }

            // Get tenant info
            const tenantData = await apiRequest(`${API}/tenants`);
            if (tenantData.tenants && tenantData.tenants.length > 0) {
                const me = tenantData.tenants[0];
                document.getElementById('userPhone').value = me.phone || '';
                document.getElementById('userUnit').value = me.house_unit || '-';
                document.getElementById('leaseProperty').value = me.property_name || '-';
                document.getElementById('leaseStart').value = me.lease_start ? new Date(me.lease_start).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'}) : 'N/A';
                document.getElementById('leaseEnd').value = me.lease_end ? new Date(me.lease_end).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'}) : 'N/A';
                
                // Next of Kin (read-only)
                document.getElementById('kinName').value = me.next_of_kin_name || '-';
                document.getElementById('kinPhone').value = me.next_of_kin_phone || '-';
                document.getElementById('kinEmail').value = me.next_of_kin_email || '-';
            }
        } catch(e) {
            console.error(e);
            if (e.message.includes('401')) window.location.href = 'signin';
        }
    }

    async function updateProfile() {
        const phone = document.getElementById('userPhone').value.trim();
        const email = document.getElementById('userEmailInput').value.trim();
        
        if (!phone) {
            toast('Phone number is required', 'error');
            return;
        }

        // Validate and sanitize email if provided
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
            // Get tenant ID first
            const tenantData = await apiRequest(`${API}/tenants`);
            
            if (!tenantData.tenants || tenantData.tenants.length === 0) {
                toast('Tenant record not found', 'error');
                return;
            }

            const tenantId = tenantData.tenants[0].id;
            
            // Update tenant phone and email
            const result = await apiRequest(`${API}/tenants/${tenantId}`, {
                method: 'PUT',
                body: JSON.stringify({
                    phone: phone,
                    email: sanitizedEmail
                })
            });
            
            toast('Profile updated successfully!', 'success');
            loadProfile(); // Reload to show updated data
        } catch(e) {
            console.error(e);
            toast(e.message || 'Failed to update profile', 'error');
        }
    }

    async function updatePassword() {
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        
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
            document.getElementById('newPassword').value = '';
            document.getElementById('confirmPassword').value = '';
        } catch(e) {
            console.error(e);
            toast(e.message || 'Failed to update password', 'error');
        }
    }

    loadProfile();
    </script>
</body>
</html>