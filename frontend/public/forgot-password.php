<?php
// No auth required - public page
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - RentFlow</title>
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
                    <span class="font-bold text-xl text-slate-900">RentFlow</span>
                </div>
                <h1 class="text-2xl font-bold text-slate-900">Forgot Password?</h1>
                <p class="text-slate-500 mt-2 text-sm">Enter your email address and we'll send you a verification code to reset your password.</p>
            </div>

            <!-- Step 1: Request Code -->
            <div id="step1">
                <form id="forgotForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Email Address</label>
                        <div class="relative">
                            <i class="fas fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input type="email" id="resetEmail" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="you@example.com" required>
                        </div>
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all">Send Reset Code</button>
                </form>
                <div class="mt-6 text-center">
                    <a href="<?php echo $basePath; ?>/signin" class="text-sm font-medium text-blue-600 hover:text-blue-700"><i class="fas fa-arrow-left mr-1"></i> Back to Login</a>
                </div>
            </div>

            <!-- Step 2: Enter Code -->
            <div id="step2" style="display:none;">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 rounded-full bg-blue-50 flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-envelope-open-text text-blue-600 text-2xl"></i>
                        </div>
                        <p class="text-sm text-slate-600">We've sent a 6-digit verification code to <strong id="sentEmail"></strong></p>
                    </div>
                <form id="verifyForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Verification Code</label>
                        <div class="relative">
                            <i class="fas fa-shield-alt absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input type="text" id="verifyCode" maxlength="6" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all tracking-widest" placeholder="000000" required>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Code expires in 15 minutes</p>
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all">Verify Code</button>
                </form>
                <div class="mt-4 text-center">
                    <button onclick="resendCode()" class="text-sm font-medium text-blue-600 hover:text-blue-700">Resend Code</button>
                </div>
            </div>

            <!-- Step 3: Reset Password -->
            <div id="step3" style="display:none;">
                <form id="resetForm" class="space-y-4">
                    <input type="hidden" id="resetEmailHidden">
                    <input type="hidden" id="resetCodeHidden">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">New Password</label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input type="password" id="newPassword" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="Min 6 characters" minlength="6" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Confirm Password</label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input type="password" id="confirmPassword" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="Confirm password" minlength="6" required>
                        </div>
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white font-semibold rounded-xl shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/40 transition-all">Reset Password</button>
                </form>
            </div>
        </div>
    </div>
    <div id="toast" class="fixed bottom-6 right-6 z-50 hidden px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2"></div>
    <script>
    const API = window.location.pathname.replace(/\/[^\/]*$/, '') + '/api';
    function toast(msg, type='success') {
        const el = document.getElementById('toast');
        const colors = { success:'bg-gradient-to-r from-emerald-500 to-emerald-600', error:'bg-gradient-to-r from-red-500 to-red-600', info:'bg-gradient-to-r from-blue-500 to-blue-600' };
        const icons = { success:'fa-check-circle', error:'fa-exclamation-circle', info:'fa-info-circle' };
        el.className = `fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2 ${colors[type]||colors.success}`;
        el.innerHTML = `<i class="fas ${icons[type]||icons.success}"></i>${msg}`;
        el.classList.remove('hidden');
        setTimeout(() => el.classList.add('hidden'), 3000);
    }

    // Step 1: Request code
    document.getElementById('forgotForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('resetEmail').value.trim();
        if (!email) { toast('Please enter your email', 'error'); return; }
        
        const btn = e.target.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Sending...';
        
        try {
            const res = await fetch(API + '/auth/forgot-password', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({email})
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.error || 'Failed to send reset code');
            
            document.getElementById('step1').style.display = 'none';
            document.getElementById('step2').style.display = 'block';
            document.getElementById('sentEmail').textContent = email;
            
            if (!data.email_sent) {
                toast('Failed to send email. Please check your email configuration.', 'error');
            } else {
                toast('Reset code sent to your email', 'success');
            }
        } catch(err) {
            toast(err.message, 'error');
            btn.disabled = false;
            btn.textContent = 'Send Reset Code';
        }
    });

    // Step 2: Verify code
    document.getElementById('verifyForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('sentEmail').textContent;
        const code = document.getElementById('verifyCode').value.trim();
        
        if (code.length !== 6) { toast('Please enter a valid 6-digit code', 'error'); return; }
        
        const btn = e.target.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Verifying...';
        
        try {
            const res = await fetch(API + '/auth/verify-reset-code', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({email, code})
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.error || 'Invalid code');
            
            document.getElementById('step2').style.display = 'none';
            document.getElementById('step3').style.display = 'block';
            document.getElementById('resetEmailHidden').value = email;
            document.getElementById('resetCodeHidden').value = code;
            toast('Code verified! Set your new password.', 'success');
        } catch(err) {
            toast(err.message, 'error');
            btn.disabled = false;
            btn.textContent = 'Verify Code';
        }
    });

    // Step 3: Reset password
    document.getElementById('resetForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('resetEmailHidden').value;
        const code = document.getElementById('resetCodeHidden').value;
        const password = document.getElementById('newPassword').value;
        const confirm = document.getElementById('confirmPassword').value;
        
        if (password !== confirm) { toast('Passwords do not match', 'error'); return; }
        if (password.length < 6) { toast('Password must be at least 6 characters', 'error'); return; }
        
        const btn = e.target.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Resetting...';
        
        try {
            const res = await fetch(API + '/auth/reset-password', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({email, code, password})
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.error || 'Failed to reset password');
            
            toast('Password reset successful! Redirecting to login...', 'success');
            setTimeout(() => window.location.href = '<?php echo $basePath; ?>/signin', 2000);
        } catch(err) {
            toast(err.message, 'error');
            btn.disabled = false;
            btn.textContent = 'Reset Password';
        }
    });

    async function resendCode() {
        const email = document.getElementById('sentEmail').textContent;
        try {
            const res = await fetch(API + '/auth/forgot-password', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({email})
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.error || 'Failed to resend code');
            toast('New code sent to your email', 'success');
        } catch(err) {
            toast(err.message, 'error');
        }
    }
    </script>
</body>
</html>