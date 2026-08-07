<?php
// No auth required - public page
require_once __DIR__ . '/../includes/base-path-fix.php';
$basePath = getBasePath();
$apiBase = $basePath . '/api';
$token = htmlspecialchars($_GET['token'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Up Your Password - RentaFlow</title>
    <base href="/">
    <link rel="stylesheet" href="/css/output.css">
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
        <div class="relative z-10 w-full max-w-md bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl p-8">
            <div class="text-center mb-8">
                <div class="flex items-center justify-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center">
                        <i class="fas fa-key text-blue-600"></i>
                    </div>
                    <span class="font-bold text-xl text-slate-900">RentaFlow</span>
                </div>
                <h1 class="text-2xl font-bold text-slate-900">Set Your Password</h1>
                <p class="text-slate-500 mt-2 text-sm">Choose a strong password to secure your account.</p>
            </div>

            <!-- Loading state while validating token -->
            <div id="loadingSection" class="text-center py-8">
                <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-spinner fa-spin text-blue-600 text-xl"></i>
                </div>
                <p class="text-slate-600 text-sm">Validating your setup link...</p>
            </div>

            <!-- Error / Expired Token Section -->
            <div id="errorSection" class="hidden">
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-4" id="errorMessage"></div>
                <div id="resendSection" class="hidden">
                    <p class="text-slate-600 text-sm text-center mb-4">Enter your email below and we'll send you a fresh setup link.</p>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Email address</label>
                            <div class="relative">
                                <i class="fas fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                <input type="email" id="resendEmail" placeholder="you@example.com" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Account type</label>
                            <div class="relative">
                                <i class="fas fa-user-tag absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                <select id="resendUserType" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all appearance-none">
                                    <option value="tenant">Tenant</option>
                                    <option value="caretaker">Caretaker</option>
                                </select>
                            </div>
                        </div>
                        <button class="w-full py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all" id="resendBtn" onclick="resendSetupEmail()">Resend Setup Link</button>
                    </div>
                </div>
            </div>

            <!-- Success Section -->
            <div id="successSection" class="hidden">
                <div class="text-center py-4">
                    <div class="w-16 h-16 rounded-full bg-emerald-50 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-check-circle text-emerald-500 text-3xl"></i>
                    </div>
                    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm mb-4" id="successMessage"></div>
                    <a href="/signin" class="inline-flex items-center gap-2 text-sm font-medium text-blue-600 hover:text-blue-700">
                        <i class="fas fa-arrow-right"></i> Go to Sign In
                    </a>
                </div>
            </div>

            <!-- Password Form Section -->
            <div id="formSection" class="hidden">
                <div id="formError" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-4 hidden"></div>

                <div class="space-y-5">
                    <div>
                        <label for="newPassword" class="block text-sm font-medium text-slate-700 mb-1.5">New Password</label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input type="password" id="newPassword" placeholder="At least 6 characters" minlength="6" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" onkeyup="checkStrength(this.value)">
                        </div>
                        <div class="mt-2">
                            <div class="flex justify-between items-center mb-1">
                                <span id="strengthLabel" class="text-xs text-slate-500">Enter a password</span>
                            </div>
                            <div class="h-1.5 bg-slate-200 rounded-full overflow-hidden">
                                <div id="strengthBar" class="h-full w-0 rounded-full transition-all duration-300" style="background:#e0e0e0"></div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="confirmPassword" class="block text-sm font-medium text-slate-700 mb-1.5">Confirm Password</label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input type="password" id="confirmPassword" placeholder="Repeat your password" minlength="6" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all">
                        </div>
                    </div>

                    <button class="w-full py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all disabled:opacity-50 disabled:cursor-not-allowed" id="submitBtn" onclick="submitSetup()">
                        Set Password & Activate Account
                    </button>
                </div>
            </div>

            <div class="mt-6 text-center">
                <a href="/signin" class="text-sm font-medium text-blue-600 hover:text-blue-700">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Sign In
                </a>
            </div>
        </div>
    </div>

    <div id="toast" class="fixed bottom-6 right-6 z-50 hidden px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2"></div>

    <script>
      const API_BASE = '<?php echo $basePath; ?>/api';

        const TOKEN = <?= json_encode($token) ?>;

        function toast(msg, type='success') {
            const el = document.getElementById('toast');
            const colors = { success:'bg-gradient-to-r from-emerald-500 to-emerald-600', error:'bg-gradient-to-r from-red-500 to-red-600', info:'bg-gradient-to-r from-blue-500 to-blue-600' };
            const icons = { success:'fa-check-circle', error:'fa-exclamation-circle', info:'fa-info-circle' };
            el.className = `fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2 ${colors[type]||colors.success}`;
            el.innerHTML = `<i class="fas ${icons[type]||icons.success}"></i>${msg}`;
            el.classList.remove('hidden');
            setTimeout(() => el.classList.add('hidden'), 3000);
        }

        async function apiCall(method, path, body) {
            const opts = {
                method,
                headers: { 'Content-Type': 'application/json' },
            };
            if (body) opts.body = JSON.stringify(body);
            const resp = await fetch(API_BASE + path, opts);
            return resp.json();
        }

        // On page load, validate the token
        document.addEventListener('DOMContentLoaded', async function() {
            if (!TOKEN) {
                showError('Missing setup token. Please use the link from your welcome email.', false);
                return;
            }

            try {
                const result = await apiCall('GET', '/auth/setup-password?token=' + encodeURIComponent(TOKEN));
                if (result.valid) {
                    showForm();
                } else {
                    showError(result.error || 'Invalid or expired setup link.', true);
                }
            } catch (e) {
                console.error('Validation error:', e);
                showError('Unable to validate your setup link. Please try again.', false);
            }
        });

        function showForm() {
            document.getElementById('loadingSection').classList.add('hidden');
            document.getElementById('errorSection').classList.add('hidden');
            document.getElementById('successSection').classList.add('hidden');
            document.getElementById('formSection').classList.remove('hidden');
        }

        function showError(message, showResend) {
            document.getElementById('loadingSection').classList.add('hidden');
            document.getElementById('formSection').classList.add('hidden');
            document.getElementById('successSection').classList.add('hidden');
            document.getElementById('errorSection').classList.remove('hidden');
            document.getElementById('errorMessage').textContent = message;
            if (showResend) {
                document.getElementById('resendSection').classList.remove('hidden');
            } else {
                document.getElementById('resendSection').classList.add('hidden');
            }
        }

        function showSuccess(message) {
            document.getElementById('loadingSection').classList.add('hidden');
            document.getElementById('formSection').classList.add('hidden');
            document.getElementById('errorSection').classList.add('hidden');
            document.getElementById('successSection').classList.remove('hidden');
            document.getElementById('successMessage').textContent = message;
        }

        function checkStrength(password) {
            const bar = document.getElementById('strengthBar');
            const label = document.getElementById('strengthLabel');
            let score = 0;
            if (password.length >= 6) score += 1;
            if (password.length >= 10) score += 1;
            if (/[A-Z]/.test(password)) score += 1;
            if (/[a-z]/.test(password)) score += 1;
            if (/[0-9]/.test(password)) score += 1;
            if (/[^A-Za-z0-9]/.test(password)) score += 1;

            const levels = [
                { min: 0, color: '#e0e0e0', width: '0%', text: 'Enter a password' },
                { min: 1, color: '#dc2626', width: '20%', text: 'Very weak' },
                { min: 2, color: '#f97316', width: '40%', text: 'Weak' },
                { min: 3, color: '#eab308', width: '60%', text: 'Fair' },
                { min: 4, color: '#22c55e', width: '80%', text: 'Strong' },
                { min: 5, color: '#16a34a', width: '100%', text: 'Very strong' },
            ];
            let level = levels[0];
            for (const l of levels) {
                if (score >= l.min) level = l;
            }
            bar.style.width = level.width;
            bar.style.background = level.color;
            label.textContent = level.text;
        }

        function sanitizeInput(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        async function submitSetup() {
            const password = document.getElementById('newPassword').value;
            const confirm = document.getElementById('confirmPassword').value;
            const errorEl = document.getElementById('formError');
            const btn = document.getElementById('submitBtn');

            const cleanPassword = sanitizeInput(password);
            const cleanConfirm = sanitizeInput(confirm);

            if (cleanPassword.length < 6) {
                errorEl.textContent = 'Password must be at least 6 characters.';
                errorEl.classList.remove('hidden');
                return;
            }
            if (cleanPassword !== cleanConfirm) {
                errorEl.textContent = 'Passwords do not match.';
                errorEl.classList.remove('hidden');
                return;
            }

            errorEl.classList.add('hidden');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Setting password...';

            try {
                const result = await apiCall('POST', '/auth/setup-password', {
                    token: TOKEN,
                    new_password: cleanPassword
                });

                if (result.success) {
                    showSuccess('Password set successfully! You can now log in with your new password.');
                    btn.innerHTML = 'Set Password & Activate Account';
                } else {
                    errorEl.textContent = result.error || 'Failed to set password. Please try again.';
                    errorEl.classList.remove('hidden');
                    btn.disabled = false;
                    btn.innerHTML = 'Set Password & Activate Account';

                    if (result.code === 'token_expired' || result.code === 'token_already_used' || result.code === 'token_invalid') {
                        showError(result.error, true);
                    }
                }
            } catch (e) {
                errorEl.textContent = 'Network error. Please check your connection and try again.';
                errorEl.classList.remove('hidden');
                btn.disabled = false;
                btn.innerHTML = 'Set Password & Activate Account';
            }
        }

        async function resendSetupEmail() {
            const email = document.getElementById('resendEmail').value.trim();
            const userType = document.getElementById('resendUserType').value;
            const btn = document.getElementById('resendBtn');

            if (!email) {
                showError('Please enter your email address.', true);
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Sending...';

            try {
                const result = await apiCall('POST', '/auth/resend-setup-email', {
                    email: email,
                    user_type: userType
                });

                if (result.email_sent) {
                    showSuccess('A new setup link has been sent to your email. Please check your inbox (and spam folder).');
                } else {
                    showError(result.error || 'Failed to send email. Please try again.', true);
                    btn.disabled = false;
                    btn.innerHTML = 'Resend Setup Link';
                }
            } catch (e) {
                showError('Network error. Please try again.', true);
                btn.disabled = false;
                btn.innerHTML = 'Resend Setup Link';
            }
        }
    </script>
</body>
</html>