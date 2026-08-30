<?php
$basePath = rtrim(str_replace('\\', '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(__DIR__, 2))), '/');

// Determine if we're in production/HTTPS environment
$isProduction = ($_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? 'production') === 'production';
$isHttps = $isProduction || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || 
           (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
           (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

// Generate CSRF token
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - RentaFlow</title>
    <base href="<?php echo $basePath; ?>/">
    <link rel="stylesheet" href="css/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-50/30 min-h-screen font-sans text-slate-800">
    <div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-blue-600 via-blue-700 to-blue-900 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <path d="M0 100 C 20 0 50 0 100 100 Z" fill="white"/>
                <path d="M0 100 C 40 20 60 20 100 100 Z" fill="white" opacity="0.5"/>
            </svg>
        </div>
    <div class="relative z-10 w-full max-w-5xl bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl overflow-hidden flex flex-col lg:flex-row">
            <div class="lg:w-5/12 bg-gradient-to-br from-blue-600 to-blue-800 p-8 lg:p-12 flex flex-col justify-between text-white relative">
                <div class="absolute inset-0 opacity-5">
                    <svg viewBox="0 0 200 200" class="w-full h-full"><path d="M0 200 C 50 0 150 0 200 200 Z" fill="white"/></svg>
                </div>
                <div class="relative">
                    <div class="flex items-center gap-3 mb-10">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center font-bold text-lg backdrop-blur">RF</div>
                        <span class="font-bold text-xl">RentalFlow</span>
                    </div>
                    <h1 class="text-3xl lg:text-4xl font-bold mb-4 leading-tight">Property Management<br/><span class="text-blue-200">Made Simple</span></h1>
                    <p class="text-blue-100/80 text-lg mb-10">Streamline your rental operations with our all-in-one platform.</p>
                </div>
                <div class="relative space-y-3">
                    <div class="flex items-center gap-3 bg-white/10 rounded-xl p-3 backdrop-blur">
                        <i class="fas fa-building text-blue-200 text-lg"></i>
                        <div><p class="font-medium text-sm">46 Properties Managed</p><p class="text-xs text-blue-200/70">Across Nairobi County</p></div>
                    </div>
                    <div class="flex items-center gap-3 bg-white/10 rounded-xl p-3 backdrop-blur">
                        <i class="fas fa-users text-blue-200 text-lg"></i>
                        <div><p class="font-medium text-sm">128 Active Tenants</p><p class="text-xs text-blue-200/70">98% Occupancy Rate</p></div>
                    </div>
                </div>
            </div>
            <div class="lg:w-7/12 p-8 lg:p-12">
                <div class="max-w-sm mx-auto">
                    <div class="text-center mb-8">
                        <img src="<?php echo $basePath; ?>/images/rentalflow-logo.png" alt="RentalFlow" class="mx-auto h-14 w-auto mb-4" onerror="this.style.display='none'">
                        <h2 class="text-2xl font-bold text-slate-900">Create Account</h2>
                        <p class="text-slate-500 mt-1">Start managing your properties</p>
                    </div>
                    <form id="registerForm" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label><input type="text" id="regName" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="John Doe" required></div>
                            <div><label class="block text-sm font-medium text-slate-700 mb-1">Phone</label><input type="tel" id="regPhone" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="+254 7XX XXX XXX"></div>
                        </div>
                        <div><label class="block text-sm font-medium text-slate-700 mb-1">Email</label><input type="email" id="regEmail" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="you@example.com" required></div>
                        <div><label class="block text-sm font-medium text-slate-700 mb-1">Password</label><input type="password" id="regPassword" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="Min 6 characters" minlength="6" required></div>
                        <div class="flex items-start gap-2">
                            <input type="checkbox" id="regTerms" class="mt-1 w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" required>
                            <label for="regTerms" class="text-sm text-slate-600">I have read and agree to the <a href="<?php echo $basePath; ?>/terms-and-conditions" target="_blank" class="text-blue-600 hover:underline font-medium">terms and conditions</a> of RentalFlow</label>
                        </div>
                        <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all">Create Account</button>
                    </form>
                    <div class="mt-6 text-center">
                        <p class="text-sm text-slate-500">Already have an account? <a href="<?php echo $basePath; ?>/signin" class="font-medium text-blue-600 hover:text-blue-700">Sign in</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="toast" class="fixed bottom-6 right-6 z-50 hidden px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2"></div>
    <script>
    const API = '<?php echo $basePath; ?>/api';
    const CSRF_TOKEN = '<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>';
    function toast(msg, type='success') {
        const el = document.getElementById('toast');
        const colors = { success:'bg-gradient-to-r from-emerald-500 to-emerald-600', error:'bg-gradient-to-r from-red-500 to-red-600', info:'bg-gradient-to-r from-blue-500 to-blue-600' };
        const icons = { success:'fa-check-circle', error:'fa-exclamation-circle', info:'fa-info-circle' };
        el.className = `fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2 ${colors[type]||colors.success}`;
        el.innerHTML = `<i class="fas ${icons[type]||icons.success}"></i>${msg}`;
        el.classList.remove('hidden');
        setTimeout(() => el.classList.add('hidden'), 3000);
    }
    document.getElementById('registerForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = e.target.querySelector('button[type="submit"]');
        const data = {
            name: document.getElementById('regName').value,
            email: document.getElementById('regEmail').value,
            password: document.getElementById('regPassword').value,
            phone: document.getElementById('regPhone').value,
            csrf_token: CSRF_TOKEN
        };
        
        btn.disabled = true;
        btn.textContent = 'Creating Account...';
        
        try {
            const res = await fetch(API + '/auth/register', {
                method: 'POST',
                headers: {
                    'Content-Type':'application/json',
                    'X-CSRF-Token': CSRF_TOKEN
                },
                body: JSON.stringify(data)
            });
            
            // Check if response is JSON before parsing
            const contentType = res.headers.get('content-type');
            let result;
            if (contentType && contentType.includes('application/json')) {
                result = await res.json();
            } else {
                const text = await res.text();
                console.error('Non-JSON response:', text);
                throw new Error('Server returned an invalid response. Please try again.');
            }
            if(!res.ok) throw new Error(result.error || 'Registration failed');
            
            // Store token in cookie only (primary auth method)
            const cookieSecure = '<?php echo $isHttps ? '; Secure' : ''; ?>';
            document.cookie = 'rf_token=' + encodeURIComponent(result.token) + '; path=/; max-age=' + (7*24*60*60) + '; SameSite=Strict' + cookieSecure;
            
            toast('Account created! Redirecting...', 'success');
            
            const base = window.location.pathname.replace(/\/[^\/]*$/, '');
            const role = result.user.role || 'owner';
            setTimeout(() => {
                const dashboard = role === 'tenant' ? base + '/tenant-dashboard' : 
                                  role === 'caretaker' ? base + '/caretaker-dashboard' : 
                                  base + '/dashboard';
                window.location.href = dashboard;
            }, 1000);
        } catch(err) { 
            btn.disabled = false;
            btn.textContent = 'Create Account';
            toast(err.message, 'error'); 
        }
    });
    </script>
</body>
</html>