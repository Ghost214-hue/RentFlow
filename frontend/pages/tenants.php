<?php
require_once __DIR__ . '/../includes/base-path-fix.php';
$basePath = $basePath ?? rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');

// Ensure auth context exists before view logic
if (!isset($user, $role, $token)) {
    if (file_exists(__DIR__ . '/../includes/auth.php')) {
        require_once __DIR__ . '/../includes/auth.php';
    }
    $user = $_SESSION['rf_user'] ?? $user ?? null;
    $role = $user['role'] ?? $role ?? 'owner';
    $token = $token ?? ($_COOKIE['rf_token'] ?? ($_SESSION['rf_token'] ?? ''));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenants - RentaFlow</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($basePath); ?>/css/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-50/30 min-h-screen font-sans text-slate-800 flex flex-col lg:flex-row">
    <?php include __DIR__ . '/../public/components/sidebar.php'; ?>
    <div class="flex-1 flex flex-col min-h-screen">
        <?php include __DIR__ . '/../public/components/header.php'; ?>
        <main class="flex-1 overflow-y-auto p-4 lg:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div><h1 class="text-2xl font-bold text-slate-900">Tenants</h1><p class="text-slate-500 mt-1">Manage your tenants</p></div>
                <?php if ($role === 'owner' || $role === 'caretaker'): ?>
                <button onclick="openTenantModal()" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all inline-flex items-center gap-2"><i class="fas fa-plus"></i>Add Tenant</button>
                <?php endif; ?>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead><tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50 bg-blue-50/50">
                            <th class="px-6 py-4">Name</th><th class="px-6 py-4">Property</th><th class="px-6 py-4">Unit</th><th class="px-6 py-4">Phone</th><th class="px-6 py-4">Balance</th><th class="px-6 py-4">Lease End</th><th class="px-6 py-4">Actions</th>
                        </tr></thead>
                        <tbody class="divide-y divide-blue-50" id="tenantsTable">
                            <tr id="loadingRow"><td colspan="7" class="px-6 py-12 text-center text-slate-400"><div class="flex flex-col items-center gap-3"><i class="fas fa-spinner fa-spin text-3xl text-blue-400"></i><span>Loading tenants...</span></div></td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="tenantsPager" class="p-4"></div>
            </div>
            <?php include __DIR__ . '/../public/components/pagination.php'; ?>
        </main>
    </div>

    <!-- TENANT ONBOARDING/EDIT WIZARD MODAL -->
    <div id="tenantModal" class="hidden rf-modal-backdrop" onclick="if(event.target===this)closeTenantModal()">
        <div class="rf-modal-panel p-5 sm:p-6 lg:p-8" onclick="event.stopPropagation()">

            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                <h3 class="text-xl font-bold text-slate-900" id="modalTitle">Add Tenant</h3>
                <p class="text-sm text-slate-500 mt-0.5">Step <span id="stepNum">1</span> of 5</p>
                </div>
                <button onclick="closeTenantModal()" class="text-slate-400 hover:text-slate-600 transition-colors"><i class="fas fa-times text-xl"></i></button>
            </div>

            <!-- Progress Indicator -->
            <div class="progress-container flex items-center mb-8 px-2">
                <div class="progress-step active text-center cursor-pointer" data-step="1" onclick="goToStep(1)">
                    <div class="step-circle mx-auto">1</div>
                    <p class="text-xs font-medium mt-1.5 text-blue-600">Personal</p>
                </div>
                <div class="step-line"></div>
                <div class="progress-step text-center cursor-pointer" data-step="2" onclick="goToStep(2)">
                    <div class="step-circle mx-auto">2</div>
                    <p class="text-xs font-medium mt-1.5 text-slate-400">ID</p>
                </div>
                <div class="step-line"></div>
                <div class="progress-step text-center cursor-pointer" data-step="3" onclick="goToStep(3)">
                    <div class="step-circle mx-auto">3</div>
                    <p class="text-xs font-medium mt-1.5 text-slate-400">Next of Kin</p>
                </div>
                <div class="step-line"></div>
                <div class="progress-step text-center cursor-pointer" data-step="4" onclick="goToStep(4)">
                    <div class="step-circle mx-auto">4</div>
                    <p class="text-xs font-medium mt-1.5 text-slate-400">Lease</p>
                </div>
                <div class="step-line"></div>
                <div class="progress-step text-center cursor-pointer" data-step="5" onclick="goToStep(5)">
                    <div class="step-circle mx-auto">5</div>
                    <p class="text-xs font-medium mt-1.5 text-slate-400">Finance</p>
                </div>
            </div>

            <form id="tenantForm" onsubmit="return false;">
                <!-- STEP 1: Personal Information -->
                <div class="step-card" id="step1">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100/50 rounded-xl p-5 border border-blue-100/80 shadow-sm">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white flex items-center justify-center text-lg"><i class="fas fa-user"></i></div>
                            <div><h4 class="font-bold text-slate-900">Personal Information</h4><p class="text-xs text-slate-500">Basic contact details of the tenant</p></div>
                        </div>
                        <div class="space-y-4">
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-50 to-blue-100 text-slate-400 flex items-center justify-center text-2xl font-bold overflow-hidden" id="profilePreview"><span id="profileInitials">?</span></div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Profile Picture</label>
                                    <div class="upload-zone rounded-xl p-3 text-center cursor-pointer" onclick="document.getElementById('profilePicInput').click()">
                                        <i class="fas fa-camera text-lg text-slate-400 mb-1"></i>
                                        <p class="text-xs text-slate-500">Click to upload photo</p>
                                        <input type="file" id="profilePicInput" class="hidden" accept=".jpg,.jpeg,.png" onchange="handleProfilePicUpload(this.files)">
                                    </div>
                                    <div id="profilePicStatus" class="text-xs text-slate-500 mt-1"></div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Full Name <span class="text-red-400">*</span></label>
                                <div class="relative">
                                    <i class="fas fa-user absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                    <input type="text" id="tenantName" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="e.g. John Mwangi Kiprop" required>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Email Address</label>
                                    <div class="relative">
                                        <i class="fas fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                        <input type="email" id="tenantEmail" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="tenant@example.com">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Phone Number <span class="text-red-400">*</span></label>
                                    <div class="relative">
                                        <i class="fas fa-phone absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                        <input type="tel" id="tenantPhone" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="+254 712 345 678" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end mt-6 pt-4 border-t border-blue-100/70">
                            <button type="button" onclick="nextStep()" class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all inline-flex items-center gap-2">Next <i class="fas fa-arrow-right text-sm"></i></button>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Identification & Documents -->
                <div class="step-card hidden" id="step2">
                    <div class="bg-gradient-to-br from-emerald-50 to-emerald-100/50 rounded-xl p-5 border border-emerald-100/80 shadow-sm">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white flex items-center justify-center text-lg"><i class="fas fa-id-card"></i></div>
                            <div><h4 class="font-bold text-slate-900">Identification</h4><p class="text-xs text-slate-500">ID details and supporting documents</p></div>
                        </div>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">ID Type</label>
                                    <div class="relative">
                                        <i class="fas fa-tag absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                        <select id="tenantIdType" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-all appearance-none">
                                            <option>National ID</option><option>Passport</option><option>Driving License</option><option>Alien ID</option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">ID Number <span class="text-red-400">*</span></label>
                                    <div class="relative">
                                        <i class="fas fa-hashtag absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                        <input type="text" id="tenantIdNumber" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-all" placeholder="ID/Passport number" required>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">KRA PIN</label>
                                <div class="relative">
                                    <i class="fas fa-hashtag absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                    <input type="text" id="tenantKraPin" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-all" placeholder="e.g. A123456789B">
                                </div>
                                <p class="text-xs text-slate-400 mt-1">Enter the tenant's KRA PIN manually. You can also upload the KRA document below.</p>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">ID Document</label>
                                    <div class="upload-zone rounded-xl p-4 text-center cursor-pointer" onclick="document.getElementById('idFileInput').click()">
                                        <i class="fas fa-cloud-upload-alt text-xl text-slate-400 mb-1"></i>
                                        <p class="text-xs text-slate-500">Click to upload ID scan</p>
                                        <input type="file" id="idFileInput" class="hidden" accept=".pdf,.jpg,.jpeg,.png" onchange="handleIdUpload(this.files)">
                                    </div>
                                    <div id="idFileStatus" class="text-xs text-slate-500 mt-1"></div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">KRA Pin Document</label>
                                    <div class="upload-zone rounded-xl p-4 text-center cursor-pointer" onclick="document.getElementById('kraFileInput').click()">
                                        <i class="fas fa-cloud-upload-alt text-xl text-slate-400 mb-1"></i>
                                        <p class="text-xs text-slate-500">Click to upload KRA pin</p>
                                        <input type="file" id="kraFileInput" class="hidden" accept=".pdf,.jpg,.jpeg,.png" onchange="handleKraUpload(this.files)">
                                    </div>
                                    <div id="kraFileStatus" class="text-xs text-slate-500 mt-1"></div>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-between mt-6 pt-4 border-t border-emerald-100/70">
                            <button type="button" onclick="prevStep()" class="px-5 py-2.5 bg-white text-slate-700 border border-slate-200 rounded-xl font-medium hover:bg-slate-50 transition-all inline-flex items-center gap-2"><i class="fas fa-arrow-left text-sm"></i> Back</button>
                            <button type="button" onclick="nextStep()" class="px-6 py-2.5 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-semibold rounded-xl shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/40 transition-all inline-flex items-center gap-2">Next <i class="fas fa-arrow-right text-sm"></i></button>
                        </div>
                    </div>
                </div>

                <!-- STEP 3: Next of Kin -->
                <div class="step-card hidden" id="step3">
                    <div class="bg-gradient-to-br from-teal-50 to-teal-100/50 rounded-xl p-5 border border-teal-100/80 shadow-sm">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-teal-500 to-teal-600 text-white flex items-center justify-center text-lg"><i class="fas fa-users"></i></div>
                            <div><h4 class="font-bold text-slate-900">Next of Kin</h4><p class="text-xs text-slate-500">Emergency contact person details</p></div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Full Name <span class="text-red-400">*</span></label>
                                <div class="relative">
                                    <i class="fas fa-user absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                    <input type="text" id="nextOfKinName" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500 outline-none transition-all" placeholder="e.g. Jane Mwangi" required>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Phone Number <span class="text-red-400">*</span></label>
                                    <div class="relative">
                                        <i class="fas fa-phone absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                        <input type="tel" id="nextOfKinPhone" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500 outline-none transition-all" placeholder="+254 712 345 678" required>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Email Address</label>
                                    <div class="relative">
                                        <i class="fas fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                        <input type="email" id="nextOfKinEmail" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500 outline-none transition-all" placeholder="nextofkin@example.com">
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Next of Kin ID Document</label>
                                    <div class="upload-zone rounded-xl p-3 text-center cursor-pointer" onclick="document.getElementById('kinIdFileInput').click()">
                                        <i class="fas fa-cloud-upload-alt text-lg text-slate-400 mb-1"></i>
                                        <p class="text-xs text-slate-500">Upload ID</p>
                                        <input type="file" id="kinIdFileInput" class="hidden" accept=".pdf,.jpg,.jpeg,.png" onchange="handleKinIdUpload(this.files)">
                                    </div>
                                    <div id="kinIdFileStatus" class="text-xs text-slate-500 mt-1"></div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Next of Kin KRA Pin</label>
                                    <div class="upload-zone rounded-xl p-3 text-center cursor-pointer" onclick="document.getElementById('kinKraFileInput').click()">
                                        <i class="fas fa-cloud-upload-alt text-lg text-slate-400 mb-1"></i>
                                        <p class="text-xs text-slate-500">Upload KRA Pin</p>
                                        <input type="file" id="kinKraFileInput" class="hidden" accept=".pdf,.jpg,.jpeg,.png" onchange="handleKinKraUpload(this.files)">
                                    </div>
                                    <div id="kinKraFileStatus" class="text-xs text-slate-500 mt-1"></div>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-between mt-6 pt-4 border-t border-teal-100/70">
                            <button type="button" onclick="prevStep()" class="px-5 py-2.5 bg-white text-slate-700 border border-slate-200 rounded-xl font-medium hover:bg-slate-50 transition-all inline-flex items-center gap-2"><i class="fas fa-arrow-left text-sm"></i> Back</button>
                            <button type="button" onclick="nextStep()" class="px-6 py-2.5 bg-gradient-to-r from-teal-500 to-teal-600 text-white font-semibold rounded-xl shadow-lg shadow-teal-500/30 hover:shadow-teal-500/40 transition-all inline-flex items-center gap-2">Next <i class="fas fa-arrow-right text-sm"></i></button>
                        </div>
                    </div>
                </div>

                <!-- STEP 4: Lease & Assignment -->
                <div class="step-card hidden" id="step4">
                    <div class="bg-gradient-to-br from-amber-50 to-amber-100/50 rounded-xl p-5 border border-amber-100/80 shadow-sm">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-amber-600 text-white flex items-center justify-center text-lg"><i class="fas fa-file-contract"></i></div>
                            <div><h4 class="font-bold text-slate-900">Lease & Unit Assignment</h4><p class="text-xs text-slate-500">Assign a property, unit, and lease period</p></div>
                        </div>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Property</label>
                                    <div class="relative">
                                        <i class="fas fa-building absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                        <select id="tenantProperty" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none transition-all appearance-none">
                                            <option value="">Select property first...</option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">House/Unit <span class="text-red-400">*</span></label>
                                    <div class="relative">
                                        <i class="fas fa-door-open absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                        <select id="tenantHouse" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none transition-all appearance-none">
                                            <option value="">Select house...</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- Available houses preview -->
                            <div id="housePreview" class="hidden">
                                <p class="text-xs font-medium text-slate-500 mb-2">Available Units</p>
                                <div id="houseList" class="space-y-1 max-h-28 overflow-y-auto"></div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Lease Start</label>
                                    <div class="relative">
                                        <i class="fas fa-calendar-alt absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                        <input type="date" id="tenantLeaseStart" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none transition-all">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Lease End</label>
                                    <div class="relative">
                                        <i class="fas fa-calendar-check absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                        <input type="date" id="tenantLeaseEnd" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none transition-all">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-between mt-6 pt-4 border-t border-amber-100/70">
                            <button type="button" onclick="prevStep()" class="px-5 py-2.5 bg-white text-slate-700 border border-slate-200 rounded-xl font-medium hover:bg-slate-50 transition-all inline-flex items-center gap-2"><i class="fas fa-arrow-left text-sm"></i> Back</button>
                            <button type="button" onclick="nextStep()" class="px-6 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 text-white font-semibold rounded-xl shadow-lg shadow-amber-500/30 hover:shadow-amber-500/40 transition-all inline-flex items-center gap-2">Next <i class="fas fa-arrow-right text-sm"></i></button>
                        </div>
                    </div>
                </div>

                <!-- STEP 5: Financial Details -->
                <div class="step-card hidden" id="step5">
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100/50 rounded-xl p-5 border border-purple-100/80 shadow-sm">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 text-white flex items-center justify-center text-lg"><i class="fas fa-money-bill-wave"></i></div>
                            <div><h4 class="font-bold text-slate-900">Financial Details</h4><p class="text-xs text-slate-500">Rent amount, deposit, and opening balance</p></div>
                        </div>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Monthly Rent (KES) <span class="text-red-400">*</span></label>
                                    <div class="relative">
                                        <i class="fas fa-file-invoice-dollar absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                        <input type="number" id="tenantRent" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-purple-500/30 focus:border-purple-500 outline-none transition-all" placeholder="45000" required>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Security Deposit (KES)</label>
                                    <div class="relative">
                                        <i class="fas fa-shield-alt absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                        <input type="number" id="tenantDeposit" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-purple-500/30 focus:border-purple-500 outline-none transition-all" placeholder="Same as rent">
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Opening Balance (KES)</label>
                                <div class="relative">
                                    <i class="fas fa-scale-balanced absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                    <input type="number" id="tenantBalance" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-purple-500/30 focus:border-purple-500 outline-none transition-all" placeholder="0" value="0">
                                </div>
                                <p class="text-xs text-slate-400 mt-1.5"><i class="fas fa-info-circle mr-1"></i>Use this if the tenant has any outstanding amount from a previous period</p>
                            </div>
                        </div>
                        <div class="flex justify-between mt-6 pt-4 border-t border-purple-100/70">
                            <button type="button" id="deleteTenantBtn" onclick="deleteTenant()" class="hidden px-5 py-2.5 bg-red-600 text-white border border-red-700 rounded-xl font-medium hover:bg-red-700 transition-all inline-flex items-center gap-2"><i class="fas fa-trash text-sm"></i> Delete Tenant</button>
                            <div class="flex gap-3 ml-auto">
                                <button type="button" onclick="prevStep()" class="px-5 py-2.5 bg-white text-slate-700 border border-slate-200 rounded-xl font-medium hover:bg-slate-50 transition-all inline-flex items-center gap-2"><i class="fas fa-arrow-left text-sm"></i> Back</button>
                                <button type="button" onclick="submitTenant()" class="px-8 py-2.5 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-semibold rounded-xl shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/40 transition-all inline-flex items-center gap-2" id="submitBtn"><i class="fas fa-check-circle text-sm"></i> <span id="submitBtnText">Register Tenant</span></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- TERMINATE TENANCY MODAL -->
    <div id="terminateModal" class="hidden rf-modal-backdrop" onclick="if(event.target===this)closeTerminateModal()">
        <div class="rf-modal-panel p-5 sm:p-6 lg:p-8 max-w-2xl" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-900">Terminate Tenancy</h3>
                <button onclick="closeTerminateModal()" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times"></i></button>
            </div>
            <form id="terminateForm" onsubmit="terminateTenant(event)">
                <input type="hidden" id="terminateTenantId">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Reason for Termination</label>
                    <textarea id="terminateReason" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-red-500/30 focus:border-red-500 outline-none transition-all text-sm" placeholder="Optional reason"></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Effective Date</label>
                    <input type="date" id="terminateDate" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-red-500/30 focus:border-red-500 outline-none transition-all text-sm">
                </div>

                <!-- Damages Section -->
                <div class="border-t border-red-100 pt-4 mb-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-semibold text-slate-700"><i class="fas fa-tools mr-1 text-amber-600"></i>Damages & Costs</h4>
                        <button type="button" onclick="addDamageRow()" class="text-xs px-3 py-1.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg hover:bg-amber-100 transition-all"><i class="fas fa-plus mr-1"></i>Add Damage</button>
                    </div>
                    <p class="text-xs text-slate-400 mb-2">Record any damages found during inspection and their repair costs.</p>
                    <div id="damagesContainer">
                        <!-- Damage rows will be added here -->
                    </div>
                    <div id="damagesTotal" class="hidden text-sm font-semibold text-slate-700 mt-2 pt-2 border-t border-slate-100">Total Damage Cost: KES <span id="damageCostTotal">0.00</span></div>
                </div>

                <p class="text-xs text-red-500 mb-4">This will make the house vacant immediately. This action cannot be undone.</p>
                <div class="flex gap-3">
                    <button type="button" onclick="closeTerminateModal()" class="flex-1 py-2 rounded-xl border border-slate-200 text-slate-600 font-medium hover:bg-slate-50 transition-all text-sm">Cancel</button>
                    <button type="submit" class="flex-1 py-2 rounded-xl bg-gradient-to-r from-red-500 to-red-600 text-white font-medium hover:from-red-600 hover:to-red-700 transition-all text-sm">Terminate</button>
                </div>
            </form>
        </div>
    </div>

    <!-- APPROVE TERMINATION MODAL -->
    <div id="approveTerminationModal" class="hidden rf-modal-backdrop" onclick="if(event.target===this)closeApproveTerminationModal()">
        <div class="rf-modal-panel p-5 sm:p-6 lg:p-8" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-900">Approve Termination Request</h3>
                <button onclick="closeApproveTerminationModal()" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times"></i></button>
            </div>
            <form id="approveTerminationForm" onsubmit="approveTermination(event)">
                <input type="hidden" id="approveTenantId">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Effective Date</label>
                    <input type="date" id="approveDate" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-all text-sm">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Reason (optional)</label>
                    <textarea id="approveReason" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-all text-sm" placeholder="Any additional notes"></textarea>
                </div>
                <p class="text-xs text-slate-500 mb-4">The tenant will be notified via email that their termination request has been approved.</p>
                <div class="flex gap-3">
                    <button type="button" onclick="closeApproveTerminationModal()" class="flex-1 py-2 rounded-xl border border-slate-200 text-slate-600 font-medium hover:bg-slate-50 transition-all text-sm">Cancel</button>
                    <button type="submit" class="flex-1 py-2 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-medium hover:from-emerald-600 hover:to-emerald-700 transition-all text-sm">Approve & Terminate</button>
                </div>
            </form>
        </div>
    </div>


    <div id="toast" class="fixed bottom-6 right-6 z-50 hidden px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2"></div>

    <script>
    // Use the server-rendered base path when available. Fallback to JS computation only if unavailable.
    const RENDERED_BASE = <?= json_encode((string)($basePath ?? '')); ?>;
    const BASE = RENDERED_BASE || (() => {
        let base = window.location.pathname;
        const idx = base.indexOf('/frontend/pages/');
        if (idx !== -1) return base.substring(0, idx);
        return base.replace(/\/[^\/]*$/, '');
    })();
    const API = BASE + '/api';
    const token = localStorage.getItem('rf_token') || '<?php echo $token; ?>';
    const headers = token ? {'Authorization':'Bearer '+token, 'Content-Type':'application/json'} : {'Content-Type':'application/json'};

    async function apiRequest(url, options = {}) {
        const res = await fetch(url, { ...options, headers });
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); } catch(e) { data = {}; }
        
        if (!res.ok) {
            if (res.status === 401) {
                localStorage.removeItem('rf_token');
                window.location.href = 'signin';
            }
            const errMsg = (data && data.error) ? data.error : text || 'Request failed';
            throw new Error(errMsg);
        }
        return data;
    }
    let tenantsCache = [];
    let uploadedDocs = [];
    let currentStep = 1;
    const totalSteps = 5;
    let editingTenantId = null;

    // tenant/kin/profile document uploads
    let tenantIdDoc = null;
    let tenantKraDoc = null;
    let kinIdDoc = null;
    let kinKraDoc = null;
    let profilePicDoc = null;

    // ==================== HELPERS ====================
    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, char => ({
            '&': '&',
            '<': '<',
            '>': '>',
            '"': '"',
            "'": '&#039;'
        }[char]));
    }

    function sanitizeEmail(email) {
        if (!email || typeof email !== 'string') return null;
        const trimmed = email.trim();
        if (!trimmed) return null;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(trimmed)) {
            toast('Invalid email format', 'error');
            return null;
        }
        return trimmed.toLowerCase();
    }

    function initials(name, fallback = '??') {
        const text = String(name || '').trim();
        if (!text) return fallback;
        return text.split(/\s+/).map(s => s[0]).join('').substring(0, 2).toUpperCase();
    }

    function formatMoney(value) {
        return (Number(value) || 0).toLocaleString();
    }

    function formatDate(value) {
        return value ? new Date(value).toLocaleDateString('en-GB', {day:'numeric', month:'short', year:'numeric'}) : 'N/A';
    }

    function toast(msg, type='success') {
        const el = document.getElementById('toast');
        const colors = { success:'bg-gradient-to-r from-emerald-500 to-emerald-600', error:'bg-gradient-to-r from-red-500 to-red-600', info:'bg-gradient-to-r from-blue-500 to-blue-600' };
        const icons = { success:'fa-check-circle', error:'fa-exclamation-circle', info:'fa-info-circle' };
        el.className = `fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2 ${colors[type]||colors.success}`;
        el.innerHTML = `<i class="fas ${icons[type]||icons.success}"></i>${escapeHtml(msg)}`;
        el.classList.remove('hidden');
        setTimeout(() => el.classList.add('hidden'), 3000);
    }

    function goToStep(step) {
        if (step < 1 || step > totalSteps) return;
        if (step > currentStep && !validateStep(currentStep)) return;
        showStep(step);
    }

    function updateProgress(step) {
        for (let i = 1; i <= totalSteps; i++) {
            const el = document.querySelector(`.progress-step[data-step="${i}"]`);
            const circle = el.querySelector('.step-circle');
            const label = el.querySelector('p');
            if (i < step) {
                el.className = 'progress-step completed text-center cursor-pointer';
                circle.innerHTML = '<i class="fas fa-check text-xs"></i>';
                label.className = 'text-xs font-medium mt-1.5 text-emerald-600';
            } else if (i === step) {
                el.className = 'progress-step active text-center cursor-pointer';
                circle.textContent = i;
                const colors = ['text-blue-600','text-emerald-600','text-amber-600','text-purple-600','text-rose-600'];
                label.className = `text-xs font-medium mt-1.5 ${colors[i-1]}`;
            } else {
                el.className = 'progress-step text-center cursor-pointer';
                circle.textContent = i;
                label.className = 'text-xs font-medium mt-1.5 text-slate-400';
            }
        }
        document.getElementById('stepNum').textContent = step;
    }

    function showStep(step) {
        for (let i = 1; i <= totalSteps; i++) {
            const el = document.getElementById('step' + i);
            if (i === step) {
                el.classList.remove('hidden');
                el.classList.add('step-card');
            } else {
                el.classList.add('hidden');
                el.classList.remove('step-card');
            }
        }
        currentStep = step;
        updateProgress(step);
        // Scroll to top of modal
        document.querySelector('#tenantModal .rf-modal-panel').scrollTop = 0;
    }

    // ==================== STEP VALIDATION ====================
    function validateStep(step) {
        if (step === 1) {
            if (!document.getElementById('tenantName').value.trim()) { toast('Please enter the tenant name', 'error'); return false; }
            if (!document.getElementById('tenantPhone').value.trim()) { toast('Please enter phone number', 'error'); return false; }
            return true;
        }
        if (step === 2) {
            if (!document.getElementById('tenantIdNumber').value.trim()) { toast('Please enter ID number', 'error'); return false; }
            return true;
        }
        if (step === 3) {
            if (!document.getElementById('nextOfKinName').value.trim()) { toast('Please enter next of kin name', 'error'); return false; }
            if (!document.getElementById('nextOfKinPhone').value.trim()) { toast('Please enter next of kin phone', 'error'); return false; }
            return true;
        }
        if (step === 4) {
            if (!document.getElementById('tenantHouse').value) { toast('Please select a house/unit', 'error'); return false; }
            return true;
        }
        if (step === 5) {
            const rent = parseFloat(document.getElementById('tenantRent').value);
            if (!rent || rent <= 0) { toast('Please enter a valid monthly rent amount', 'error'); return false; }
            return true;
        }
        return true;
    }

    function nextStep() {
        if (!validateStep(currentStep)) return;
        if (currentStep === 5) {
            // Final step: submit via button, do not advance
            return;
        }
        showStep(currentStep + 1);
    }

    function prevStep() {
        showStep(currentStep - 1);
    }

    // ==================== LOAD TENANTS TABLE ====================
    async function loadTenants() {
        const tbody = document.getElementById('tenantsTable');
        try {
            // Show loading spinner
            tbody.innerHTML = '<tr id="loadingRow"><td colspan="7" class="px-6 py-12 text-center text-slate-400"><div class="flex flex-col items-center gap-3"><i class="fas fa-spinner fa-spin text-3xl text-blue-400"></i><span>Loading tenants...</span></div></td></tr>';
            
            const data = await apiRequest(`${API}/tenants`);
            tenantsCache = data.tenants || [];
            if (data.tenants && data.tenants.length) {
                tbody.innerHTML = data.tenants.map(t => `
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="px-6 py-4"><a href="<?php echo $basePath; ?>/tenant-details?id=${Number(t.id)||0}" class="flex items-center gap-2 text-inherit hover:text-inherit"><div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold overflow-hidden">${t.profile_picture ? '<img src="' + escapeHtml(t.profile_picture) + '" class="w-full h-full object-cover" alt="">' : escapeHtml(initials(t.name))}</div><div><p class="text-sm font-medium text-slate-900">${escapeHtml(t.name || 'N/A')}</p><p class="text-xs text-slate-400">${escapeHtml(t.email || '')}</p></div></a></td>
                        <td class="px-6 py-4 text-sm text-slate-600">${escapeHtml(t.property_name || 'N/A')}</td>
                        <td class="px-6 py-4 text-sm font-medium text-slate-900">${escapeHtml(t.house_unit || 'N/A')}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">${escapeHtml(t.phone || 'N/A')}</td>
                        <td class="px-6 py-4 text-sm font-medium ${Number(t.balance) > 0 ? 'text-amber-600' : 'text-emerald-600'}">KES ${formatMoney(t.balance)}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">${escapeHtml(formatDate(t.lease_end))}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <a href="<?php echo $basePath; ?>/tenant-details?id=${Number(t.id)||0}" class="p-1.5 text-slate-400 hover:text-blue-600 transition-colors inline-flex items-center justify-center" aria-label="View tenant details"><i class="fas fa-eye"></i></a>
                                <button onclick="openTenantDetails(${Number(t.id)||0})" class="p-1.5 text-slate-400 hover:text-emerald-600 transition-colors" aria-label="Edit tenant"><i class="fas fa-pen"></i></button>
                                ${t.status === 'pending_termination' ? `<span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Pending</span>` : ''}
                                <?php if ($role === 'owner'): ?>
                                ${t.status !== 'terminated' && t.status !== 'pending_termination' ? `<button onclick="openTerminateModal(${Number(t.id)||0})" class="p-1.5 text-slate-400 hover:text-red-600 transition-colors" aria-label="Terminate tenancy"><i class="fas fa-door-open"></i></button>` : ''}
                                ${t.status === 'pending_termination' ? `<button onclick="openApproveTerminationModal(${Number(t.id)||0})" class="p-1.5 text-slate-400 hover:text-emerald-600 transition-colors" aria-label="Approve termination"><i class="fas fa-check"></i></button>` : ''}
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-12 text-center text-slate-400">No tenants found. Click "Add Tenant" to register one.</td></tr>';
            }
        } catch(e) {
            console.error('loadTenants error:', e);
            document.getElementById('tenantsTable').innerHTML = `<tr><td colspan="7" class="px-6 py-12 text-center text-red-400">Error: ${escapeHtml(e.message)}</td></tr>`;
        }
    }

    // ==================== LOAD PROPERTIES ====================
    async function loadProperties() {
        const select = document.getElementById('tenantProperty');
        try {
            // Show loading state
            select.innerHTML = '<option value="">Loading properties...</option>';
            select.disabled = true;
            
            const data = await apiRequest(`${API}/properties`);
            if (data.properties && data.properties.length) {
                select.innerHTML = '<option value="">Select property...</option>' + data.properties.map(p => `<option value="${Number(p.id)||0}">${escapeHtml(p.name)} (${Number(p.units)||0} units)</option>`).join('');
            } else {
                select.innerHTML = '<option value="">No properties available</option>';
            }
        } catch(e) { 
            console.error('loadProperties:', e); 
            select.innerHTML = '<option value="">Failed to load</option>';
            toast('Could not load properties: ' + e.message, 'error'); 
        } finally {
            select.disabled = false;
        }
    }

    // ==================== LOAD HOUSES (on property change) ====================
    document.getElementById('tenantProperty').addEventListener('change', async function() {
        const propId = this.value;
        const houseSelect = document.getElementById('tenantHouse');
        const housePreview = document.getElementById('housePreview');
        const houseList = document.getElementById('houseList');
        
        if (!propId) {
            houseSelect.innerHTML = '<option value="">Select house...</option>';
            housePreview.classList.add('hidden');
            return;
        }
        
        houseSelect.innerHTML = '<option value="">Loading...</option>';
        houseSelect.disabled = true;
        try {
            const data = await apiRequest(`${API}/houses/available?property_id=${propId}`);
            
            if (data.houses && data.houses.length) {
                houseSelect.innerHTML = '<option value="">Select house...</option>' + data.houses.map(h => 
                    `<option value="${Number(h.id)||0}" data-rent="${Number(h.rent)||0}" data-unit="${escapeHtml(h.unit || '')}" data-type="${escapeHtml(h.type || '')}">${escapeHtml(h.unit)} - ${escapeHtml(h.type)} (KES ${formatMoney(h.rent)})</option>`
                ).join('');
                // Show preview
                housePreview.classList.remove('hidden');
                houseList.innerHTML = data.houses.map(h => `
                    <div class="house-option flex items-center justify-between p-2 rounded-lg border border-blue-100 bg-white text-sm cursor-pointer hover:bg-blue-50" onclick="selectHouseFromList(${Number(h.id)||0}, event)">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center"><i class="fas fa-door-open text-xs text-blue-500"></i></div>
                            <span class="font-medium text-slate-800">${escapeHtml(h.unit)}</span>
                            <span class="text-xs text-slate-400">${escapeHtml(h.type)}</span>
                        </div>
                        <span class="text-sm font-semibold text-slate-900">KES ${formatMoney(h.rent)}</span>
                    </div>
                `).join('');
            } else {
                houseSelect.innerHTML = '<option value="">No vacant units available</option>';
                housePreview.classList.add('hidden');
            }
        } catch(e) { 
            console.error(e); 
            houseSelect.innerHTML = '<option value="">Failed to load</option>';
        } finally {
            houseSelect.disabled = false;
        }
    });

    function selectHouseFromList(id, clickEvent) {
        document.getElementById('tenantHouse').value = id;
        const selected = document.querySelector(`#tenantHouse option[value="${id}"]`);
        const unit = selected?.dataset.unit || 'unit';
        const type = selected?.dataset.type || '';
        const rent = Number(selected?.dataset.rent) || 0;
        document.getElementById('tenantRent').value = rent || 0;
        toast(`Selected ${unit}${type ? ' - ' + type : ''} (KES ${formatMoney(rent)})`, 'info');
        // Highlight in dropdown
        document.querySelectorAll('.house-option').forEach(el => el.classList.remove('selected', 'bg-blue-50', 'border-blue-300'));
        clickEvent.currentTarget.classList.add('selected', 'bg-blue-50', 'border-blue-300');
    }

    // ==================== AUTO-FILL RENT ====================
    document.getElementById('tenantHouse').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const rent = selected ? selected.dataset.rent : 0;
        if (rent) document.getElementById('tenantRent').value = rent;
    });

    // ==================== FILE UPLOAD HELPERS ====================
    async function uploadFile(file) {
        const formData = new FormData();
        formData.append('file', file);
        const url = `${API}/upload`;
        const authHeader = headers.Authorization ? { Authorization: headers.Authorization } : {};
        const res = await fetch(url, {
            method: 'POST',
            body: formData,
            headers: authHeader
        });
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); } catch(e) { data = {}; }
        if (!res.ok) {
            const errMsg = (data && data.error) ? data.error : text || 'Request failed';
            throw new Error(errMsg);
        }
        return data;
    }

    async function handleIdUpload(files) {
        const status = document.getElementById('idFileStatus');
        try {
            status.textContent = 'Uploading...';
            tenantIdDoc = (await uploadFile(files[0])).file;
            status.textContent = 'Uploaded: ' + tenantIdDoc.name;
            toast('ID document uploaded', 'success');
        } catch(err) {
            status.textContent = '';
            toast(err.message, 'error');
        }
    }

    async function handleKraUpload(files) {
        const status = document.getElementById('kraFileStatus');
        try {
            status.textContent = 'Uploading...';
            tenantKraDoc = (await uploadFile(files[0])).file;
            status.textContent = 'Uploaded: ' + tenantKraDoc.name;
            toast('KRA document uploaded', 'success');
        } catch(err) {
            status.textContent = '';
            toast(err.message, 'error');
        }
    }

    async function handleKinIdUpload(files) {
        const status = document.getElementById('kinIdFileStatus');
        try {
            status.textContent = 'Uploading...';
            kinIdDoc = (await uploadFile(files[0])).file;
            status.textContent = 'Uploaded: ' + kinIdDoc.name;
            toast('Next of kin ID uploaded', 'success');
        } catch(err) {
            status.textContent = '';
            toast(err.message, 'error');
        }
    }

    async function handleKinKraUpload(files) {
        const status = document.getElementById('kinKraFileStatus');
        try {
            status.textContent = 'Uploading...';
            kinKraDoc = (await uploadFile(files[0])).file;
            status.textContent = 'Uploaded: ' + kinKraDoc.name;
            toast('Next of kin KRA uploaded', 'success');
        } catch(err) {
            status.textContent = '';
            toast(err.message, 'error');
        }
    }

    async function handleProfilePicUpload(files) {
        const status = document.getElementById('profilePicStatus');
        const preview = document.getElementById('profilePreview');
        try {
            status.textContent = 'Uploading...';
            profilePicDoc = (await uploadFile(files[0])).file;
            status.textContent = 'Uploaded: ' + profilePicDoc.name;
            preview.innerHTML = `<img src="${profilePicDoc.url}" class="w-full h-full object-cover" alt="Profile">`;
            preview.classList.add('bg-white');
            toast('Profile picture uploaded', 'success');
        } catch(err) {
            status.textContent = '';
            toast(err.message, 'error');
        }
    }

    function renderUploadedFiles() {
        // No-op: docs tab removed; individual file statuses are shown per input
    }

    function removeFile(index) {
        // No-op: docs tab removed
    }

    // Summary UI removed; keeping function as harmless no-op in case it's referenced elsewhere
    function updateSummary() {}

    // ==================== MODAL CONTROL ====================
    function openTenantModal() {
        editingTenantId = null;
        currentStep = 1;
        showStep(1);
        document.getElementById('modalTitle').textContent = 'Add Tenant';
        document.getElementById('submitBtnText').textContent = 'Register Tenant';
        document.getElementById('deleteTenantBtn').classList.add('hidden');
        document.getElementById('tenantModal').classList.remove('hidden');
        loadProperties();
        // Reset form
        document.getElementById('tenantForm').reset();
        uploadedDocs = [];
        tenantIdDoc = null;
        tenantKraDoc = null;
        kinIdDoc = null;
        kinKraDoc = null;
        document.getElementById('housePreview').classList.add('hidden');
        document.getElementById('tenantHouse').innerHTML = '<option value="">Select house...</option>';
        document.getElementById('idFileStatus').textContent = '';
        document.getElementById('kraFileStatus').textContent = '';
        document.getElementById('kinIdFileStatus').textContent = '';
        document.getElementById('kinKraFileStatus').textContent = '';
    }
    
    function closeTenantModal() {
        document.getElementById('tenantModal').classList.add('hidden');
        editingTenantId = null;
    }

    function openTenantDetails(id) {
        const tenant = tenantsCache.find(t => Number(t.id) === Number(id));
        if (!tenant) {
            toast('Tenant details are not available right now.', 'error');
            return;
        }
        
        editingTenantId = id;
        currentStep = 1;
        showStep(1);
        
        // Update modal title and buttons
        document.getElementById('modalTitle').textContent = 'Edit Tenant';
        document.getElementById('submitBtnText').textContent = 'Update Tenant';
        document.getElementById('deleteTenantBtn').classList.remove('hidden');
        
        // Populate all form fields with tenant data
        document.getElementById('tenantName').value = tenant.name || '';
        document.getElementById('tenantEmail').value = tenant.email || '';
        document.getElementById('tenantPhone').value = tenant.phone || '';
        document.getElementById('tenantIdType').value = tenant.id_type || 'National ID';
        document.getElementById('tenantIdNumber').value = tenant.id_number || '';
        document.getElementById('nextOfKinName').value = tenant.next_of_kin_name || '';
        document.getElementById('nextOfKinPhone').value = tenant.next_of_kin_phone || '';
        document.getElementById('nextOfKinEmail').value = tenant.next_of_kin_email || '';
        document.getElementById('tenantLeaseStart').value = tenant.lease_start || '';
        document.getElementById('tenantLeaseEnd').value = tenant.lease_end || '';
        document.getElementById('tenantRent').value = tenant.rent || 0;
        document.getElementById('tenantDeposit').value = tenant.deposit || 0;
        document.getElementById('tenantBalance').value = tenant.balance || 0;
        
        // Reset document uploads
        tenantIdDoc = null;
        tenantKraDoc = null;
        kinIdDoc = null;
        kinKraDoc = null;
        uploadedDocs = [];
        if (tenant.documents) {
            try {
                const docs = JSON.parse(tenant.documents);
                if (Array.isArray(docs)) uploadedDocs = docs;
            } catch(e) {
                uploadedDocs = [];
            }
        }
        renderUploadedFiles();
        document.getElementById('idFileStatus').textContent = '';
        document.getElementById('kraFileStatus').textContent = '';
        document.getElementById('kinIdFileStatus').textContent = '';
        document.getElementById('kinKraFileStatus').textContent = '';
        
        // Load properties first, then set house after a short delay
        document.getElementById('tenantModal').classList.remove('hidden');
        loadProperties();
        
        // Set property and house after properties load
        if (tenant.property_id) {
            setTimeout(() => {
                document.getElementById('tenantProperty').value = tenant.property_id;
                // Trigger house load
                document.getElementById('tenantProperty').dispatchEvent(new Event('change'));
                // Set house after houses load
                setTimeout(() => {
                    if (tenant.house_id) {
                        document.getElementById('tenantHouse').value = tenant.house_id;
                    }
                }, 500);
            }, 100);
        }
    }

    function closeTenantDetailsModal() {
        closeTenantModal();
    }

    // ==================== SUBMIT TENANT (ADD/UPDATE) ====================
    async function submitTenant() {
        const btn = event.target;
        const btnText = document.getElementById('submitBtnText');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + (editingTenantId ? 'Updating...' : 'Registering...');
        
        const houseSelect = document.getElementById('tenantHouse');
        const sanitizedEmail = sanitizeEmail(document.getElementById('tenantEmail').value);
        const sanitizedKinEmail = sanitizeEmail(document.getElementById('nextOfKinEmail').value);
        
        if (sanitizedEmail === null && document.getElementById('tenantEmail').value.trim() !== '') {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle text-sm"></i> ' + (editingTenantId ? 'Update Tenant' : 'Register Tenant');
            return;
        }
        
            const docs = [...uploadedDocs];
            if (tenantIdDoc) docs.push(tenantIdDoc);
            if (tenantKraDoc) docs.push(tenantKraDoc);
            if (kinIdDoc) docs.push(kinIdDoc);
            if (kinKraDoc) docs.push(kinKraDoc);

            const data = {
            name: document.getElementById('tenantName').value.trim(),
            email: sanitizedEmail,
            phone: document.getElementById('tenantPhone').value.trim(),
            id_type: document.getElementById('tenantIdType').value,
            id_number: document.getElementById('tenantIdNumber').value.trim(),
            id_kra_pin: document.getElementById('tenantKraPin').value.trim() || null,
            profile_picture: profilePicDoc ? profilePicDoc.url : null,
            next_of_kin_name: document.getElementById('nextOfKinName').value.trim() || null,
            next_of_kin_phone: document.getElementById('nextOfKinPhone').value.trim() || null,
            next_of_kin_email: sanitizedKinEmail,
            property_id: parseInt(document.getElementById('tenantProperty').value) || null,
            house_id: parseInt(houseSelect.value) || null,
            lease_start: document.getElementById('tenantLeaseStart').value || null,
            lease_end: document.getElementById('tenantLeaseEnd').value || null,
            rent: parseFloat(document.getElementById('tenantRent').value) || 0,
            deposit: parseFloat(document.getElementById('tenantDeposit').value) || 0,
            balance: parseFloat(document.getElementById('tenantBalance').value) || 0,
            documents: docs.length ? JSON.stringify(docs) : null,
        };

        // Final validation
        if (!data.name) { toast('Name is required', 'error'); btn.disabled = false; btn.innerHTML = '<i class="fas fa-check-circle text-sm"></i> ' + (editingTenantId ? 'Update Tenant' : 'Register Tenant'); return; }
        if (!data.phone) { toast('Phone is required', 'error'); btn.disabled = false; btn.innerHTML = '<i class="fas fa-check-circle text-sm"></i> ' + (editingTenantId ? 'Update Tenant' : 'Register Tenant'); return; }
        if (!data.id_number) { toast('ID number is required', 'error'); btn.disabled = false; btn.innerHTML = '<i class="fas fa-check-circle text-sm"></i> ' + (editingTenantId ? 'Update Tenant' : 'Register Tenant'); return; }
        if (!data.house_id) { toast('Please assign a house/unit', 'error'); btn.disabled = false; btn.innerHTML = '<i class="fas fa-check-circle text-sm"></i> ' + (editingTenantId ? 'Update Tenant' : 'Register Tenant'); return; }
        if (!data.rent || data.rent <= 0) { toast('Please enter a valid rent amount', 'error'); btn.disabled = false; btn.innerHTML = '<i class="fas fa-check-circle text-sm"></i> ' + (editingTenantId ? 'Update Tenant' : 'Register Tenant'); return; }

        try {
            console.log(editingTenantId ? 'Updating tenant:' : 'Submitting tenant:', data);
            
            const url = editingTenantId ? `${API}/tenants/${editingTenantId}` : `${API}/tenants`;
            const method = editingTenantId ? 'PUT' : 'POST';
            
            const result = await apiRequest(url, {
                method,
                body: JSON.stringify(data)
            });
            
            toast(editingTenantId ? 'Tenant updated successfully!' : 'Tenant registered successfully! Unit assigned and bill generated.', 'success');
            closeTenantModal();
            loadTenants();
        } catch(err) {
            console.error(editingTenantId ? 'Update tenant error:' : 'Submit tenant error:', err);
            toast(err.message, 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle text-sm"></i> ' + (editingTenantId ? 'Update Tenant' : 'Register Tenant');
        }
    }

    // ==================== DELETE TENANT ====================
    async function deleteTenant() {
        if (!editingTenantId) return;
        
        if (!confirm('Are you sure you want to delete this tenant? This action will: \n\n1. Free up the assigned house/unit\n2. Delete all tenant records\n3. Cannot be undone\n\nType "DELETE" to confirm or cancel.')) {
            return;
        }

        const confirmText = prompt('Type "DELETE" to confirm deletion:');
        if (confirmText !== 'DELETE') {
            toast('Deletion cancelled', 'info');
            return;
        }
        
        try {
            const result = await apiRequest(`${API}/tenants/${editingTenantId}`, {
                method: 'DELETE'
            });
            
            toast('Tenant deleted successfully', 'success');
            closeTenantModal();
            loadTenants();
        } catch(err) {
            console.error('Delete tenant error:', err);
            toast(err.message || 'Could not delete tenant', 'error');
        }
    }

    // ==================== DAMAGE ROW HELPERS ====================
    let damageRowCount = 0;

    function addDamageRow() {
        damageRowCount++;
        const container = document.getElementById('damagesContainer');
        const div = document.createElement('div');
        div.className = 'damage-row grid grid-cols-1 sm:grid-cols-5 gap-2 p-3 rounded-lg border border-amber-100 bg-amber-50/30 mb-2';
        div.id = 'damageRow_' + damageRowCount;
        div.innerHTML = `
            <div class="sm:col-span-2"><input type="text" class="damage-title w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/30 outline-none" placeholder="Damage title*"></div>
            <div class="sm:col-span-1"><input type="text" class="damage-desc w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/30 outline-none" placeholder="Description"></div>
            <div class="sm:col-span-1"><div class="relative"><span class="absolute left-2 top-1/2 -translate-y-1/2 text-xs text-slate-400">KES</span><input type="number" class="damage-cost w-full pl-10 pr-2 py-1.5 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/30 outline-none" placeholder="Cost" min="0" step="0.01" oninput="updateDamageTotal()"></div></div>
            <div class="sm:col-span-1 flex items-center gap-1"><input type="text" class="damage-vendor w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/30 outline-none" placeholder="Vendor"><button type="button" onclick="this.closest('.damage-row').remove(); updateDamageTotal();" class="text-red-400 hover:text-red-600 p-1"><i class="fas fa-times"></i></button></div>
        `;
        container.appendChild(div);
        document.getElementById('damagesTotal').classList.remove('hidden');
        updateDamageTotal();
    }

    function updateDamageTotal() {
        let total = 0;
        document.querySelectorAll('.damage-cost').forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        document.getElementById('damageCostTotal').textContent = total.toLocaleString('en-KE', {minimumFractionDigits: 2});
    }

    function getDamagesData() {
        const damages = [];
        document.querySelectorAll('.damage-row').forEach(row => {
            const title = row.querySelector('.damage-title')?.value?.trim();
            if (title) {
                damages.push({
                    title: title,
                    description: row.querySelector('.damage-desc')?.value?.trim() || '',
                    cost: parseFloat(row.querySelector('.damage-cost')?.value) || 0,
                    vendor_name: row.querySelector('.damage-vendor')?.value?.trim() || '',
                    priority: 'medium'
                });
            }
        });
        return damages;
    }

    // ==================== TERMINATION HANDLERS ====================
    function openTerminateModal(tenantId) {
        // Clear damage rows
        document.getElementById('damagesContainer').innerHTML = '';
        document.getElementById('damagesTotal').classList.add('hidden');
        damageRowCount = 0;
        
        document.getElementById('terminateTenantId').value = tenantId;
        const d = new Date();
        d.setDate(d.getDate() + 1); // Default tomorrow
        document.getElementById('terminateDate').value = d.toISOString().split('T')[0];
        document.getElementById('terminateReason').value = '';
        document.getElementById('terminateModal').classList.remove('hidden');
    }

    function closeTerminateModal() {
        document.getElementById('terminateModal').classList.add('hidden');
    }

    async function terminateTenant(event) {
        event.preventDefault();
        const tenantId = document.getElementById('terminateTenantId').value;
        const reason = document.getElementById('terminateReason').value.trim();
        const effectiveDate = document.getElementById('terminateDate').value;
        const damages = getDamagesData();
        const btn = event.target.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Terminating...';
        
        try {
            const result = await apiRequest(`${API}/tenants/${tenantId}/terminate`, {
                method: 'POST',
                body: JSON.stringify({ reason, effective_date: effectiveDate, damages })
            });
            toast('Tenancy terminated successfully. House is now vacant.', 'success');
            closeTerminateModal();
            loadTenants();
        } catch (err) {
            toast(err.message || 'Failed to terminate tenancy', 'error');
            btn.disabled = false;
            btn.textContent = 'Terminate';
        }
    }

    function openApproveTerminationModal(tenantId) {
        document.getElementById('approveTenantId').value = tenantId;
        const d = new Date();
        document.getElementById('approveDate').value = d.toISOString().split('T')[0];
        document.getElementById('approveReason').value = '';
        document.getElementById('approveTerminationModal').classList.remove('hidden');
    }

    function closeApproveTerminationModal() {
        document.getElementById('approveTerminationModal').classList.add('hidden');
    }

    async function approveTermination(event) {
        event.preventDefault();
        const tenantId = document.getElementById('approveTenantId').value;
        const effectiveDate = document.getElementById('approveDate').value;
        const reason = document.getElementById('approveReason').value.trim();
        const btn = event.target.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Approving...';
        
        try {
            const result = await apiRequest(`${API}/tenants/${tenantId}/terminate`, {
                method: 'POST',
                body: JSON.stringify({ reason, effective_date: effectiveDate })
            });
            toast('Termination approved. Tenant notified via email.', 'success');
            closeApproveTerminationModal();
            loadTenants();
        } catch (err) {
            toast(err.message || 'Failed to approve termination', 'error');
            btn.disabled = false;
            btn.textContent = 'Approve & Terminate';
        }
    }

    // ==================== INIT ====================
    loadTenants();
    </script>
</body>
</html>