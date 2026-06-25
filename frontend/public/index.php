<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/">
    <title>RentFlow - Property Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        primary: { 50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd', 400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a' },
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="css/output.css">
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-50/30 min-h-screen text-slate-800">
    <div id="app"></div>

<script>
// ==================== API CLIENT ====================
// Detect base path for subdirectory deployment (e.g. /RentFlow/)
const BASE_PATH = window.location.pathname.replace(/\/[^\/]*$/, '') || '';
const API_BASE = BASE_PATH + '/api';
const Api = {
    token: localStorage.getItem('rf_token') || null,
    setToken(t) { this.token = t; t ? localStorage.setItem('rf_token', t) : localStorage.removeItem('rf_token'); },
    headers() { const h = {'Content-Type':'application/json'}; if(this.token) h['Authorization']=`Bearer ${this.token}`; return h; },
    async req(m, e, d) {
        const cfg = { method: m, headers: this.headers() };
        if(d) cfg.body = JSON.stringify(d);
        const r = await fetch(`${API_BASE}${e}`, cfg);
        const j = await r.json();
        if(!r.ok) throw new Error(j.error || 'Request failed');
        return j;
    },
    login: (e,p) => Api.req('POST','/auth/login',{email:e,password:p}),
    register: (d) => Api.req('POST','/auth/register',d),
    getProfile: () => Api.req('GET','/auth/me'),
    getDashboard: () => Api.req('GET','/dashboard'),
    getProperties: () => Api.req('GET','/properties'),
    getProperty: (i) => Api.req('GET',`/properties/${i}`),
    createProperty: (d) => Api.req('POST','/properties',d),
    updateProperty: (i,d) => Api.req('PUT',`/properties/${i}`,d),
    deleteProperty: (i) => Api.req('DELETE',`/properties/${i}`),
    getHouses: (p) => { const q = new URLSearchParams(p).toString(); return Api.req('GET',`/houses${q?'?'+q:''}`); },
    createHouse: (d) => Api.req('POST','/houses',d),
    updateHouse: (i,d) => Api.req('PUT',`/houses/${i}`,d),
    deleteHouse: (i) => Api.req('DELETE',`/houses/${i}`),
    getTenants: () => Api.req('GET','/tenants'),
    getTenant: (i) => Api.req('GET',`/tenants/${i}`),
    createTenant: (d) => Api.req('POST','/tenants',d),
    updateTenant: (i,d) => Api.req('PUT',`/tenants/${i}`,d),
    deleteTenant: (i) => Api.req('DELETE',`/tenants/${i}`),
    getPayments: () => Api.req('GET','/payments'),
    createPayment: (d) => Api.req('POST','/payments',d),
    getBills: (m) => Api.req('GET',`/bills${m?'?month='+m:''}`),
    generateBills: (d) => Api.req('POST','/bills/generate',d),
    getComplaints: (p) => { const q = new URLSearchParams(p).toString(); return Api.req('GET',`/complaints${q?'?'+q:''}`); },
    getComplaint: (i) => Api.req('GET',`/complaints/${i}`),
    createComplaint: (d) => Api.req('POST','/complaints',d),
    updateComplaint: (i,d) => Api.req('PUT',`/complaints/${i}`,d),
    getCommunications: () => Api.req('GET','/communications'),
    sendCommunication: (d) => Api.req('POST','/communications',d),
    getTemplates: () => Api.req('GET','/templates'),
    getCaretakers: () => Api.req('GET','/caretakers'),
    createCaretaker: (d) => Api.req('POST','/caretakers',d),
    getReports: () => Api.req('GET','/reports'),
};

// ==================== STATE ====================
const Store = {
    user: null,
    screen: 'login',
    sidebarOpen: false,
    loading: false,
    data: {},
    modals: [],
    toast: null,
};

// ==================== UTILITIES ====================
const $ = (s) => document.querySelector(s);
const $$ = (s) => document.querySelectorAll(s);
const fmtCurrency = (n) => { const v = Number(n); return isNaN(v) ? 'KES 0' : 'KES ' + v.toLocaleString(); };
const fmtDate = (d) => { try { return new Date(d).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'}); } catch(e) { return 'N/A'; } };
const initials = (n) => { if(!n) return '??'; return n.split(' ').map(s=>s[0]).join('').substring(0,2).toUpperCase(); };
const statusColor = (s) => ({
    'paid':'bg-emerald-100 text-emerald-700','completed':'bg-emerald-100 text-emerald-700',
    'partial':'bg-amber-100 text-amber-700','pending':'bg-slate-100 text-slate-600',
    'open':'bg-red-100 text-red-700','in-progress':'bg-blue-100 text-blue-700',
    'resolved':'bg-emerald-100 text-emerald-700','vacant':'bg-slate-100 text-slate-500',
    'occupied':'bg-emerald-100 text-emerald-700','active':'bg-blue-100 text-blue-700',
}[s] || 'bg-slate-100 text-slate-600');

const toast = (msg, type='success') => {
    Store.toast = { msg, type, id: Date.now() };
    render();
    setTimeout(() => { Store.toast = null; render(); }, 3000);
};

const navigate = (screen, pushHistory = true) => { 
    Store.screen = screen; 
    Store.sidebarOpen = false; 
    render(); 
    if(pushHistory) {
        const path = screen === 'dashboard' ? '/' : '/' + screen;
        window.history.pushState({ screen }, '', path);
    }
};

const openModal = (name, data) => { Store.modals.push({ name, data }); render(); };
const closeModal = () => { Store.modals.pop(); render(); };

// ==================== LOGIN SCREEN ====================
const LoginScreen = () => `
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
                    <span class="font-bold text-xl">RentFlow</span>
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
                    <h2 class="text-2xl font-bold text-slate-900">Welcome back</h2>
                    <p class="text-slate-500 mt-1">Sign in to your account</p>
                </div>
                <form onsubmit="handleLogin(event)" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                        <div class="relative">
                            <i class="fas fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input type="email" id="loginEmail" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="you@example.com" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input type="password" id="loginPassword" class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="Enter password" required>
                            <button type="button" onclick="togglePass()" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"><i class="fas fa-eye" id="passIcon"></i></button>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"><span class="text-sm text-slate-600">Remember me</span></label>
                        <a href="#" onclick="toast('Reset link sent','info')" class="text-sm font-medium text-blue-600 hover:text-blue-700">Forgot password?</a>
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 hover:from-blue-700 hover:to-blue-800 transition-all">Sign In</button>
                </form>
                <div class="mt-6 text-center">
                    <p class="text-sm text-slate-500">Don't have an account? <a href="#" onclick="showRegister()" class="font-medium text-blue-600 hover:text-blue-700">Sign up</a></p>
                </div>
                <div class="mt-6 p-4 bg-gradient-to-r from-blue-50 to-blue-100/50 rounded-xl border border-blue-100">
                    <p class="text-xs font-semibold text-blue-600 uppercase tracking-wider mb-2">Demo Credentials</p>
                    <div class="space-y-1 text-xs text-slate-600">
                        <p><span class="font-medium text-blue-600">Owner:</span> owner@rentflow.co / admin123</p>
                        <p><span class="font-medium text-blue-600">Caretaker:</span> caretaker@rentflow.co / caretaker123</p>
                        <p><span class="font-medium text-blue-600">Tenant:</span> tenant@rentflow.co / tenant123</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>`;

// ==================== REGISTER SCREEN ====================
const RegisterScreen = () => `
<div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-blue-600 via-blue-700 to-blue-900 relative overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
            <path d="M0 100 C 20 0 50 0 100 100 Z" fill="white"/>
        </svg>
    </div>
    <div class="relative z-10 w-full max-w-lg bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl p-8 lg:p-10">
        <div class="text-center mb-6">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-600 to-blue-700 flex items-center justify-center text-white font-bold text-lg mx-auto mb-3">RF</div>
            <h2 class="text-2xl font-bold text-slate-900">Create Account</h2>
            <p class="text-slate-500 mt-1">Start managing your properties</p>
        </div>
        <form onsubmit="handleRegister(event)" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label><input type="text" id="regName" class="input" placeholder="John Doe" required></div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Phone</label><input type="tel" id="regPhone" class="input" placeholder="+254 7XX XXX XXX"></div>
            </div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Email</label><input type="email" id="regEmail" class="input" placeholder="you@example.com" required></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Password</label><input type="password" id="regPassword" class="input" placeholder="Min 6 characters" minlength="6" required></div>
            <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all">Create Account</button>
        </form>
        <p class="text-center text-sm text-slate-500 mt-4">Already have an account? <a href="#" onclick="Store.screen='login';render()" class="font-medium text-blue-600 hover:text-blue-700">Sign in</a></p>
    </div>
</div>`;

// ==================== SIDEBAR ====================
const Sidebar = () => {
    const nav = [
        { id:'dashboard', label:'Dashboard', icon:'fa-chart-line' },
        { id:'properties', label:'Properties', icon:'fa-building' },
        { id:'houses', label:'Houses', icon:'fa-home' },
        { id:'tenants', label:'Tenants', icon:'fa-users' },
        { id:'billing', label:'Billing', icon:'fa-file-invoice-dollar' },
        { id:'payments', label:'Payments', icon:'fa-money-bill-wave' },
        { id:'complaints', label:'Complaints', icon:'fa-exclamation-triangle' },
        { id:'communications', label:'Messages', icon:'fa-comments' },
        { id:'reports', label:'Reports', icon:'fa-chart-pie' },
        { id:'settings', label:'Settings', icon:'fa-cog' },
    ];
    return `
    <aside class="${Store.sidebarOpen ? 'translate-x-0' : '-translate-x-full'} lg:translate-x-0 fixed lg:static inset-y-0 left-0 z-40 w-64 bg-gradient-to-b from-blue-700 to-blue-900 shadow-2xl transition-transform duration-300 flex flex-col">
        <div class="h-16 flex items-center px-5 border-b border-white/10">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-white font-bold text-sm backdrop-blur">RF</div>
                <span class="font-bold text-lg text-white">RentFlow</span>
            </div>
            <button onclick="Store.sidebarOpen=false;render()" class="lg:hidden ml-auto text-white/60 hover:text-white"><i class="fas fa-times text-xl"></i></button>
        </div>
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-0.5 scrollbar-thin">
            ${nav.map(item => `
                <button onclick="navigate('${item.id}')" class="sidebar-link w-full text-left ${Store.screen === item.id ? 'active' : ''}">
                    <i class="fas ${item.icon} w-5 text-center"></i>${item.label}
                </button>
            `).join('')}
        </nav>
        <div class="p-4 border-t border-white/10">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-white/20 text-white flex items-center justify-center font-semibold text-sm">${initials(Store.user?.name)}</div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">${Store.user?.name}</p>
                    <p class="text-xs text-white/60 capitalize">${Store.user?.role}</p>
                </div>
                <button onclick="logout()" class="text-white/50 hover:text-white transition-colors" title="Logout"><i class="fas fa-sign-out-alt"></i></button>
            </div>
        </div>
    </aside>
    ${Store.sidebarOpen ? '<div onclick="Store.sidebarOpen=false;render()" class="fixed inset-0 bg-black/30 z-30 lg:hidden"></div>' : ''}
    `;
};

// ==================== HEADER ====================
const Header = () => `
<header class="h-16 bg-white/80 backdrop-blur-lg border-b border-blue-100 flex items-center justify-between px-4 lg:px-8 sticky top-0 z-20">
    <div class="flex items-center gap-4">
        <button onclick="Store.sidebarOpen=true;render()" class="lg:hidden p-2 text-slate-500 hover:bg-blue-50 rounded-xl"><i class="fas fa-bars"></i></button>
        <div class="hidden md:flex items-center gap-2 text-sm text-slate-500">
            <span class="capitalize">${Store.user?.role} Portal</span>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-slate-800 font-medium capitalize">${Store.screen.replace('-',' ')}</span>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <div class="hidden md:flex items-center bg-blue-50 rounded-xl px-3 py-1.5">
            <i class="fas fa-search text-slate-400 text-sm mr-2"></i>
            <input type="text" placeholder="Search..." class="bg-transparent border-none outline-none text-sm w-40 text-slate-700 placeholder-slate-400">
        </div>
        <button onclick="navigate('settings')" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-colors"><i class="fas fa-bell"></i></button>
    </div>
</header>`;

// ==================== DASHBOARD ====================
const DashboardScreen = async () => {
    Store.loading = true; render();
    try {
        const res = await Api.getDashboard();
        Store.data.dashboard = res;
    } catch(e) { toast(e.message, 'error'); }
    Store.loading = false; render();
    setTimeout(() => initCharts(), 100);
};

const DashboardContent = () => {
    const d = Store.data.dashboard || {};
    const p = d.properties || {};
    const h = d.houses || {};
    return `
    <div class="p-4 lg:p-8 space-y-6 page-enter">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
                <p class="text-slate-500 mt-1">Welcome back, ${Store.user?.name}. Here's your overview.</p>
            </div>
            <button onclick="navigate('properties')" class="btn-primary"><i class="fas fa-plus"></i>Add Property</button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="card p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center"><i class="fas fa-building text-blue-600"></i></div>
                    <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded-full">${p.total || 0} total</span>
                </div>
                <p class="text-2xl font-bold text-slate-900">${p.total || 0}</p>
                <p class="text-sm text-slate-500">Properties</p>
            </div>
            <div class="card p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100 flex items-center justify-center"><i class="fas fa-door-open text-emerald-600"></i></div>
                    <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">${h.total ? Math.round((h.occupied||0)/(h.total||1)*100)+'%' : '0%'}</span>
                </div>
                <p class="text-2xl font-bold text-slate-900">${h.occupied||0}/${h.total||0}</p>
                <p class="text-sm text-slate-500">Occupied Units</p>
            </div>
            <div class="card p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center"><i class="fas fa-money-bill-wave text-blue-600"></i></div>
                    <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded-full">This month</span>
                </div>
                <p class="text-2xl font-bold text-slate-900">${fmtCurrency(d.revenue||0)}</p>
                <p class="text-sm text-slate-500">Monthly Revenue</p>
            </div>
            <div class="card p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-50 to-amber-100 flex items-center justify-center"><i class="fas fa-exclamation-circle text-amber-600"></i></div>
                    <span class="text-xs font-medium text-amber-600 bg-amber-50 px-2 py-1 rounded-full">Outstanding</span>
                </div>
                <p class="text-2xl font-bold text-slate-900">${fmtCurrency(d.outstanding||0)}</p>
                <p class="text-sm text-slate-500">Outstanding Rent</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 card p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-slate-900">Revenue Overview</h3>
                    <select class="text-sm border border-blue-100 rounded-xl px-3 py-1.5 bg-white text-slate-600 outline-none"><option>Last 6 Months</option></select>
                </div>
                <div class="relative h-72"><canvas id="revenueChart"></canvas></div>
            </div>
            <div class="card p-5">
                <h3 class="font-semibold text-slate-900 mb-4">Occupancy</h3>
                <div class="relative h-48"><canvas id="occChart"></canvas></div>
                <div class="mt-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2"><div class="w-3 h-3 rounded-full bg-blue-500"></div><span class="text-sm text-slate-600">Occupied</span></div>
                        <span class="text-sm font-medium text-slate-900">${h.occupied||0} units</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2"><div class="w-3 h-3 rounded-full bg-slate-200"></div><span class="text-sm text-slate-600">Vacant</span></div>
                        <span class="text-sm font-medium text-slate-900">${(h.total||0)-(h.occupied||0)} units</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="card p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-slate-900">Recent Payments</h3>
                    <button onclick="navigate('payments')" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View All</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead><tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50">
                            <th class="pb-3 pr-4">Tenant</th><th class="pb-3 pr-4">Amount</th><th class="pb-3 pr-4">Type</th><th class="pb-3 pr-4">Date</th><th class="pb-3">Status</th>
                        </tr></thead>
                        <tbody class="divide-y divide-blue-50">
                            ${(d.recentPayments||[]).map(p => `
                            <tr class="hover:bg-blue-50/30 transition-colors">
                                <td class="py-3 pr-4"><div class="flex items-center gap-2"><div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">${initials(p.tenant_name)}</div><span class="text-sm font-medium text-slate-900">${p.tenant_name||'N/A'}</span></div></td>
                                <td class="py-3 pr-4 text-sm font-medium text-slate-900">${fmtCurrency(p.amount)}</td>
                                <td class="py-3 pr-4 text-sm text-slate-500">${p.type}</td>
                                <td class="py-3 pr-4 text-sm text-slate-500">${fmtDate(p.date)}</td>
                                <td class="py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium ${statusColor(p.status)}">${p.status}</span></td>
                            </tr>`).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-slate-900">Active Complaints</h3>
                    <span class="px-2 py-1 bg-red-50 text-red-600 text-xs font-medium rounded-full">${(d.activeComplaints||[]).length} Open</span>
                </div>
                <div class="space-y-3">
                    ${(d.activeComplaints||[]).slice(0,4).map(c => `
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-blue-50/50 border border-blue-100/50">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-amber-50 to-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0"><i class="fas fa-exclamation text-xs"></i></div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-0.5"><p class="text-sm font-medium text-slate-900 truncate">${c.title}</p><span class="text-xs text-slate-400">${fmtDate(c.date)}</span></div>
                            <p class="text-xs text-slate-500 mb-1">${c.tenant_name||'N/A'} • ${c.unit||''}</p>
                            <span class="px-2 py-0.5 rounded text-xs font-medium ${statusColor(c.status)}">${c.status}</span>
                        </div>
                    </div>`).join('')}
                </div>
                <button onclick="navigate('complaints')" class="w-full mt-3 py-2 text-sm text-blue-600 font-medium hover:bg-blue-50 rounded-xl transition-colors">View All Complaints</button>
            </div>
        </div>
    </div>`;
};

// ==================== PROPERTIES ====================
const PropertiesScreen = async () => {
    Store.loading = true; render();
    try { Store.data.properties = (await Api.getProperties()).properties; } catch(e) { toast(e.message,'error'); }
    Store.loading = false; render();
};

const PropertiesContent = () => {
    const props = Store.data.properties || [];
    return `
    <div class="p-4 lg:p-8 space-y-6 page-enter">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div><h1 class="text-2xl font-bold text-slate-900">Properties</h1><p class="text-slate-500 mt-1">Manage your rental properties</p></div>
            <button onclick="openModal('property')" class="btn-primary"><i class="fas fa-plus"></i>Add Property</button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            ${props.map(p => `
            <div class="card overflow-hidden group">
                <div class="relative h-48 overflow-hidden bg-gradient-to-br from-blue-100 to-blue-200">
                    ${p.image ? `<img src="${p.image}" alt="${p.name}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">` : `<div class="w-full h-full flex items-center justify-center"><i class="fas fa-building text-4xl text-blue-300"></i></div>`}
                    <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
                    <div class="absolute bottom-4 left-4 right-4"><h3 class="text-lg font-bold text-white">${p.name}</h3><p class="text-sm text-white/80"><i class="fas fa-map-marker-alt mr-1"></i>${p.address}</p></div>
                    <div class="absolute top-4 right-4"><span class="px-2 py-1 bg-white/90 backdrop-blur rounded-lg text-xs font-medium text-slate-700">${p.type||'N/A'}</span></div>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div class="text-center"><p class="text-lg font-bold text-slate-900">${p.unit_count||p.units||0}</p><p class="text-xs text-slate-500">Units</p></div>
                        <div class="text-center"><p class="text-lg font-bold text-emerald-600">${p.occupied_count||p.occupied||0}</p><p class="text-xs text-slate-500">Occupied</p></div>
                        <div class="text-center"><p class="text-lg font-bold text-slate-900">${fmtCurrency(p.rent||0)}</p><p class="text-xs text-slate-500">Avg Rent</p></div>
                    </div>
                    <div class="flex items-center gap-2 mb-4">
                        <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden"><div class="h-full bg-gradient-to-r from-blue-500 to-blue-600 rounded-full" style="width:${((p.occupied_count||p.occupied||0)/(p.unit_count||p.units||1))*100}%"></div></div>
                        <span class="text-xs text-slate-500">${Math.round(((p.occupied_count||p.occupied||0)/(p.unit_count||p.units||1))*100)}%</span>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="viewProperty(${p.id})" class="flex-1 py-2 text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-xl transition-colors">Manage</button>
                        <button onclick="openModal('house',${p.id})" class="px-3 py-2 text-sm font-medium text-slate-600 bg-slate-50 hover:bg-slate-100 rounded-xl transition-colors"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
            </div>`).join('')}
        </div>
    </div>`;
};

// ==================== HOUSES ====================
const HousesScreen = async () => {
    Store.loading = true; render();
    try { Store.data.houses = (await Api.getHouses()).houses; } catch(e) { toast(e.message,'error'); }
    Store.loading = false; render();
};

const HousesContent = () => {
    const houses = Store.data.houses || [];
    return `
    <div class="p-4 lg:p-8 space-y-6 page-enter">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div><h1 class="text-2xl font-bold text-slate-900">Houses & Units</h1><p class="text-slate-500 mt-1">Manage individual units</p></div>
            <button onclick="openModal('house')" class="btn-primary"><i class="fas fa-plus"></i>Add Unit</button>
        </div>
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead><tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50 bg-blue-50/50">
                        <th class="px-6 py-4">Unit</th><th class="px-6 py-4">Property</th><th class="px-6 py-4">Type</th><th class="px-6 py-4">Tenant</th><th class="px-6 py-4">Rent</th><th class="px-6 py-4">Status</th><th class="px-6 py-4">Actions</th>
                    </tr></thead>
                    <tbody class="divide-y divide-blue-50">
                        ${houses.map(h => `
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            <td class="px-6 py-4 font-medium text-slate-900">${h.unit}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">${h.property_name||'N/A'}</td>
                            <td class="px-6 py-4 text-sm text-slate-500">${h.type}</td>
                            <td class="px-6 py-4">${h.tenant_name ? `<div class="flex items-center gap-2"><div class="w-6 h-6 rounded-full bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">${initials(h.tenant_name)}</div><span class="text-sm text-slate-700">${h.tenant_name}</span></div>` : '<span class="text-sm text-slate-400">-</span>'}</td>
                            <td class="px-6 py-4 text-sm font-medium text-slate-900">${fmtCurrency(h.rent)}</td>
                            <td class="px-6 py-4"><span class="px-2 py-1 rounded-full text-xs font-medium ${statusColor(h.status)}">${h.status}</span></td>
                            <td class="px-6 py-4"><button onclick="toast('Edit house','info')" class="p-1.5 text-slate-400 hover:text-blue-600 transition-colors"><i class="fas fa-edit"></i></button></td>
                        </tr>`).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    </div>`;
};

// ==================== TENANTS ====================
const TenantsScreen = async () => {
    Store.loading = true; render();
    try { Store.data.tenants = (await Api.getTenants()).tenants; } catch(e) { toast(e.message,'error'); }
    Store.loading = false; render();
};

const TenantsContent = () => {
    const tenants = Store.data.tenants || [];
    return `
    <div class="p-4 lg:p-8 space-y-6 page-enter">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div><h1 class="text-2xl font-bold text-slate-900">Tenants</h1><p class="text-slate-500 mt-1">Manage your tenants</p></div>
            <button onclick="openModal('tenant')" class="btn-primary"><i class="fas fa-plus"></i>Add Tenant</button>
        </div>
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead><tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50 bg-blue-50/50">
                        <th class="px-6 py-4">Name</th><th class="px-6 py-4">Property</th><th class="px-6 py-4">Unit</th><th class="px-6 py-4">Phone</th><th class="px-6 py-4">Balance</th><th class="px-6 py-4">Lease End</th><th class="px-6 py-4">Actions</th>
                    </tr></thead>
                    <tbody class="divide-y divide-blue-50">
                        ${tenants.map(t => `
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            <td class="px-6 py-4"><div class="flex items-center gap-2"><div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">${initials(t.name)}</div><div><p class="text-sm font-medium text-slate-900">${t.name}</p><p class="text-xs text-slate-400">${t.email||''}</p></div></div></td>
                            <td class="px-6 py-4 text-sm text-slate-600">${t.property_name||'N/A'}</td>
                            <td class="px-6 py-4 text-sm font-medium text-slate-900">${t.house_unit||'N/A'}</td>
                            <td class="px-6 py-4 text-sm text-slate-500">${t.phone||'N/A'}</td>
                            <td class="px-6 py-4 text-sm font-medium ${t.balance > 0 ? 'text-amber-600' : 'text-emerald-600'}">${fmtCurrency(t.balance||0)}</td>
                            <td class="px-6 py-4 text-sm text-slate-500">${t.lease_end ? fmtDate(t.lease_end) : 'N/A'}</td>
                            <td class="px-6 py-4"><button onclick="toast('View tenant','info')" class="p-1.5 text-slate-400 hover:text-blue-600 transition-colors"><i class="fas fa-eye"></i></button></td>
                        </tr>`).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    </div>`;
};

// ==================== PAYMENTS ====================
const PaymentsScreen = async () => {
    Store.loading = true; render();
    try { Store.data.payments = (await Api.getPayments()).payments; } catch(e) { toast(e.message,'error'); }
    Store.loading = false; render();
};

const PaymentsContent = () => {
    const payments = Store.data.payments || [];
    return `
    <div class="p-4 lg:p-8 space-y-6 page-enter">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div><h1 class="text-2xl font-bold text-slate-900">Payments</h1><p class="text-slate-500 mt-1">All payment records</p></div>
            <button onclick="openModal('payment')" class="btn-primary"><i class="fas fa-plus"></i>Record Payment</button>
        </div>
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead><tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50 bg-blue-50/50">
                        <th class="px-6 py-4">Receipt</th><th class="px-6 py-4">Tenant</th><th class="px-6 py-4">Description</th><th class="px-6 py-4">Amount</th><th class="px-6 py-4">Method</th><th class="px-6 py-4">Date</th><th class="px-6 py-4">Status</th>
                    </tr></thead>
                    <tbody class="divide-y divide-blue-50">
                        ${payments.map(p => `
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-blue-600">${p.receipt||'N/A'}</td>
                            <td class="px-6 py-4"><div class="flex items-center gap-2"><div class="w-6 h-6 rounded-full bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">${initials(p.tenant_name)}</div><span class="text-sm text-slate-700">${p.tenant_name||'N/A'}</span></div></td>
                            <td class="px-6 py-4 text-sm text-slate-600">${p.description||''}</td>
                            <td class="px-6 py-4 text-sm font-bold text-slate-900">${fmtCurrency(p.amount)}</td>
                            <td class="px-6 py-4 text-sm text-slate-500">${p.method||'N/A'}</td>
                            <td class="px-6 py-4 text-sm text-slate-500">${fmtDate(p.date)}</td>
                            <td class="px-6 py-4"><span class="px-2 py-0.5 rounded-full text-xs font-medium ${statusColor(p.status)}">${p.status}</span></td>
                        </tr>`).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    </div>`;
};

// ==================== COMPLAINTS ====================
const ComplaintsScreen = async () => {
    Store.loading = true; render();
    try { Store.data.complaints = (await Api.getComplaints()).complaints; } catch(e) { toast(e.message,'error'); }
    Store.loading = false; render();
};

const ComplaintsContent = () => {
    const complaints = Store.data.complaints || [];
    return `
    <div class="p-4 lg:p-8 space-y-6 page-enter">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div><h1 class="text-2xl font-bold text-slate-900">Complaints</h1><p class="text-slate-500 mt-1">Track maintenance issues</p></div>
            <button onclick="openModal('complaint')" class="btn-primary"><i class="fas fa-plus"></i>New Complaint</button>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            ${complaints.map(c => `
            <div class="card p-5 hover:shadow-md transition-shadow cursor-pointer" onclick="toast('Viewing complaint','info')">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-50 to-amber-100 text-amber-600 flex items-center justify-center"><i class="fas fa-wrench"></i></div>
                        <div><h3 class="font-medium text-slate-900">${c.title}</h3><p class="text-xs text-slate-500">${c.category||'Other'} • ${c.property_name||''} ${c.unit||''}</p></div>
                    </div>
                    <span class="px-2 py-1 rounded-full text-xs font-medium ${statusColor(c.status)}">${c.status}</span>
                </div>
                <p class="text-sm text-slate-600 mb-4 line-clamp-2">${c.description||''}</p>
                <div class="flex items-center justify-between pt-3 border-t border-blue-50">
                    <div class="flex items-center gap-2"><div class="w-6 h-6 rounded-full bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">${initials(c.tenant_name)}</div><span class="text-xs text-slate-500">${c.tenant_name||'N/A'}</span></div>
                    <span class="text-xs text-slate-400"><i class="far fa-clock mr-1"></i>${fmtDate(c.date)}</span>
                </div>
            </div>`).join('')}
        </div>
    </div>`;
};

// ==================== SETTINGS ====================
const SettingsContent = () => `
<div class="p-4 lg:p-8 max-w-3xl mx-auto page-enter">
    <h1 class="text-2xl font-bold text-slate-900 mb-6">Settings</h1>
    <div class="card p-6 mb-6">
        <h3 class="font-semibold text-slate-900 mb-4">Profile</h3>
        <div class="flex items-center gap-4 mb-6">
            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 text-white flex items-center justify-center text-2xl font-bold">${initials(Store.user?.name)}</div>
            <div><p class="font-medium text-slate-900">${Store.user?.name}</p><p class="text-sm text-slate-500">${Store.user?.email}</p></div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Name</label><input type="text" value="${Store.user?.name||''}" class="input"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Email</label><input type="email" value="${Store.user?.email||''}" class="input"></div>
        </div>
    </div>
    <div class="card p-6">
        <h3 class="font-semibold text-slate-900 mb-4">Security</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-700 mb-1">New Password</label><input type="password" class="input" placeholder="Enter new password"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Confirm</label><input type="password" class="input" placeholder="Confirm password"></div>
        </div>
        <button onclick="toast('Password updated','success')" class="btn-primary mt-4">Update Password</button>
    </div>
</div>`;

// ==================== MODALS ====================
const Modal = () => {
    if(!Store.modals.length) return '';
    const m = Store.modals[Store.modals.length-1];
    let content = '';
    if(m.name === 'property') content = `
        <h3 class="text-lg font-bold text-slate-900 mb-4">Add Property</h3>
        <div class="space-y-4">
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Name</label><input type="text" class="input" placeholder="e.g. Sunrise Apartments"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Address</label><input type="text" class="input" placeholder="Full address"></div>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Type</label><select class="select"><option>Apartment Block</option><option>Townhouses</option><option>Studio</option><option>Villa</option></select></div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Units</label><input type="number" class="input" placeholder="0"></div>
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button onclick="closeModal()" class="btn-secondary">Cancel</button>
                <button onclick="saveProperty()" class="btn-primary">Save</button>
            </div>
        </div>`;
    else if(m.name === 'house') content = `
        <h3 class="text-lg font-bold text-slate-900 mb-4">Add Unit</h3>
        <div class="space-y-4">
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Property</label><select class="select"><option>Select property...</option></select></div>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Unit</label><input type="text" class="input" placeholder="e.g. A1"></div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Type</label><select class="select"><option>Studio</option><option>1 Bedroom</option><option>2 Bedroom</option><option>3 Bedroom</option></select></div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Rent (KES)</label><input type="number" class="input" placeholder="45000"></div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Status</label><select class="select"><option>Vacant</option><option>Occupied</option></select></div>
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button onclick="closeModal()" class="btn-secondary">Cancel</button>
                <button onclick="toast('Unit added','success');closeModal()" class="btn-primary">Save</button>
            </div>
        </div>`;
    else if(m.name === 'tenant') content = `
        <h3 class="text-lg font-bold text-slate-900 mb-4">Add Tenant</h3>
        <div class="space-y-4">
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label><input type="text" class="input" placeholder="Tenant name"></div>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Email</label><input type="email" class="input" placeholder="email@example.com"></div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Phone</label><input type="tel" class="input" placeholder="+254 7XX XXX XXX"></div>
            </div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Assign House</label><select class="select"><option>Select house...</option></select></div>
            <div class="flex justify-end gap-3 pt-4">
                <button onclick="closeModal()" class="btn-secondary">Cancel</button>
                <button onclick="toast('Tenant registered','success');closeModal()" class="btn-primary">Save</button>
            </div>
        </div>`;
    else if(m.name === 'payment') content = `
        <h3 class="text-lg font-bold text-slate-900 mb-4">Record Payment</h3>
        <div class="space-y-4">
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Tenant</label><select class="select"><option>Select tenant...</option></select></div>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Type</label><select class="select"><option>Rent</option><option>Water</option><option>Electricity</option></select></div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Amount</label><input type="number" class="input" placeholder="0.00"></div>
            </div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Method</label><select class="select"><option>M-Pesa</option><option>Bank Transfer</option><option>Cash</option></select></div>
            <div class="flex justify-end gap-3 pt-4">
                <button onclick="closeModal()" class="btn-secondary">Cancel</button>
                <button onclick="toast('Payment recorded','success');closeModal()" class="btn-primary">Save</button>
            </div>
        </div>`;
    else if(m.name === 'complaint') content = `
        <h3 class="text-lg font-bold text-slate-900 mb-4">New Complaint</h3>
        <div class="space-y-4">
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Title</label><input type="text" class="input" placeholder="Brief description"></div>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Category</label><select class="select"><option>Plumbing</option><option>Electrical</option><option>Security</option><option>Other</option></select></div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Priority</label><select class="select"><option>Low</option><option>Medium</option><option>High</option></select></div>
            </div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Description</label><textarea rows="3" class="input" placeholder="Describe the issue..."></textarea></div>
            <div class="flex justify-end gap-3 pt-4">
                <button onclick="closeModal()" class="btn-secondary">Cancel</button>
                <button onclick="toast('Complaint submitted','success');closeModal()" class="btn-primary">Submit</button>
            </div>
        </div>`;

    return `
    <div class="modal-overlay" onclick="closeModal()">
        <div class="modal-content p-6" onclick="event.stopPropagation()">
            <button onclick="closeModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition-colors"><i class="fas fa-times text-xl"></i></button>
            ${content}
        </div>
    </div>`;
};

// ==================== LOADING ====================
const Loading = () => `
<div class="flex items-center justify-center py-20">
    <div class="flex flex-col items-center gap-4">
        <div class="w-10 h-10 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
        <p class="text-sm text-slate-500">Loading...</p>
    </div>
</div>`;

// ==================== TOAST ====================
const Toast = () => {
    if(!Store.toast) return '';
    const colors = { success:'bg-gradient-to-r from-emerald-500 to-emerald-600', error:'bg-gradient-to-r from-red-500 to-red-600', info:'bg-gradient-to-r from-blue-500 to-blue-600' };
    const icons = { success:'fa-check-circle', error:'fa-exclamation-circle', info:'fa-info-circle' };
    return `<div class="fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2 animate-slide-in ${colors[Store.toast.type]||colors.success}">
        <i class="fas ${icons[Store.toast.type]||icons.success}"></i>${Store.toast.msg}
    </div>`;
};

// ==================== RENDER ====================
const render = () => {
    const app = document.getElementById('app');
    if(!Store.user || Store.screen === 'login') {
        app.innerHTML = Store.screen === 'register' ? RegisterScreen() : LoginScreen();
        app.innerHTML += Toast();
        return;
    }

    let content = '';
    if(Store.loading) content = Loading();
    else {
        switch(Store.screen) {
            case 'dashboard': content = DashboardContent(); break;
            case 'properties': content = PropertiesContent(); break;
            case 'houses': content = HousesContent(); break;
            case 'tenants': content = TenantsContent(); break;
            case 'payments': content = PaymentsContent(); break;
            case 'complaints': content = ComplaintsContent(); break;
            case 'settings': content = SettingsContent(); break;
            default: content = DashboardContent();
        }
    }

    app.innerHTML = `
        <div class="flex h-screen overflow-hidden">
            ${Sidebar()}
            <div class="flex-1 flex flex-col overflow-hidden">
                ${Header()}
                <main class="flex-1 overflow-y-auto scrollbar-thin bg-gradient-to-br from-blue-50 via-white to-blue-50/30">
                    ${content}
                </main>
            </div>
        </div>
        ${Modal()}
        ${Toast()}
    `;
};

// ==================== ACTIONS ====================
const handleLogin = async (e) => {
    e.preventDefault();
    const email = $('#loginEmail').value;
    const password = $('#loginPassword').value;
    try {
        const res = await Api.login(email, password);
        Api.setToken(res.token);
        Store.user = res.user;
        Store.screen = 'dashboard';
        toast(`Welcome back, ${res.user.name}!`);
        render();
        DashboardScreen();
    } catch(err) {
        toast(err.message, 'error');
    }
};

const handleRegister = async (e) => {
    e.preventDefault();
    const data = {
        name: $('#regName').value,
        email: $('#regEmail').value,
        password: $('#regPassword').value,
        phone: $('#regPhone').value,
    };
    try {
        const res = await Api.register(data);
        Api.setToken(res.token);
        Store.user = res.user;
        Store.screen = 'dashboard';
        toast('Account created successfully!');
        render();
        DashboardScreen();
    } catch(err) {
        toast(err.message, 'error');
    }
};

const logout = () => {
    Api.setToken(null);
    Store.user = null;
    Store.screen = 'login';
    Store.data = {};
    toast('Logged out');
    render();
};

const showRegister = () => { Store.screen = 'register'; render(); };
const togglePass = () => {
    const inp = $('#loginPassword');
    const icon = $('#passIcon');
    if(inp.type === 'password') { inp.type = 'text'; icon.classList.replace('fa-eye','fa-eye-slash'); }
    else { inp.type = 'password'; icon.classList.replace('fa-eye-slash','fa-eye'); }
};

const viewProperty = (id) => toast('Property details','info');

const saveProperty = async () => {
    closeModal();
    toast('Property added','success');
    PropertiesScreen();
};

// ==================== CHARTS ====================
const initCharts = () => {
    const revCtx = document.getElementById('revenueChart');
    if(revCtx) {
        new Chart(revCtx, {
            type: 'line',
            data: {
                labels: ['Aug','Sep','Oct','Nov','Dec','Jan'],
                datasets: [{
                    label: 'Revenue',
                    data: [320000,335000,310000,350000,380000,395000],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.1)',
                    borderWidth: 2, fill: true, tension: 0.4,
                    pointRadius: 4, pointBackgroundColor: '#3b82f6'
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { color: '#64748b' } },
                    x: { grid: { display: false }, ticks: { color: '#64748b' } }
                }
            }
        });
    }
    const occCtx = document.getElementById('occChart');
    if(occCtx) {
        new Chart(occCtx, {
            type: 'doughnut',
            data: {
                labels: ['Occupied','Vacant'],
                datasets: [{ data: [38, 6], backgroundColor: ['#3b82f6', '#e2e8f0'], borderWidth: 0 }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { display: false } } }
        });
    }
};

// ==================== ROUTING ====================
const pathToScreen = (path) => {
    const p = path.replace(/^\/+|\/+$/g, '') || 'dashboard';
    const validScreens = ['dashboard', 'properties', 'houses', 'tenants', 'billing', 'payments', 'complaints', 'communications', 'reports', 'settings'];
    return validScreens.includes(p) ? p : 'dashboard';
};

// Handle browser back/forward buttons
window.addEventListener('popstate', (e) => {
    if(e.state && e.state.screen) {
        Store.screen = e.state.screen;
        Store.sidebarOpen = false;
        render();
        // Load screen data
        switch(Store.screen) {
            case 'dashboard': DashboardScreen(); break;
            case 'properties': PropertiesScreen(); break;
            case 'houses': HousesScreen(); break;
            case 'tenants': TenantsScreen(); break;
            case 'payments': PaymentsScreen(); break;
            case 'complaints': ComplaintsScreen(); break;
        }
    }
});

// ==================== INIT ====================
// Auto-login if token exists
const init = async () => {
    if(Api.token) {
        try {
            const res = await Api.getProfile();
            Store.user = res.user;
            Store.screen = pathToScreen(window.location.pathname);
            render();
            // Load data for initial screen
            switch(Store.screen) {
                case 'dashboard': DashboardScreen(); break;
                case 'properties': PropertiesScreen(); break;
                case 'houses': HousesScreen(); break;
                case 'tenants': TenantsScreen(); break;
                case 'payments': PaymentsScreen(); break;
                case 'complaints': ComplaintsScreen(); break;
                default: DashboardScreen();
            }
            return;
        } catch(e) {
            Api.setToken(null);
        }
    }
    render();
};
init();
</script>
</body>
</html>
