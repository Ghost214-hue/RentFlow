<?php
require_once __DIR__ . '/../includes/auth.php';
if ($userRole !== 'owner') { header('Location: /signin'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenants - RentFlow</title>
    <link rel="stylesheet" href="/css/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .step-card {
            animation: fadeIn 0.35s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        .step-card.exit {
            animation: fadeOut 0.25s ease-in forwards;
        }
        @keyframes fadeOut {
            from { opacity: 1; transform: translateX(0); }
            to { opacity: 0; transform: translateX(-20px); }
        }
        .progress-step {
            transition: all 0.3s ease;
        }
        .progress-step.active .step-circle {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            border-color: #2563eb;
            box-shadow: 0 4px 12px rgba(37,99,235,0.3);
            transform: scale(1.1);
        }
        .progress-step.completed .step-circle {
            background: #059669;
            color: white;
            border-color: #059669;
        }
        .progress-step.completed .step-line {
            background: #059669;
        }
        .progress-step.active .step-line {
            background: linear-gradient(to right, #059669, #2563eb);
        }
        .step-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 700;
            border: 2.5px solid #e2e8f0;
            background: white;
            color: #94a3b8;
            transition: all 0.3s ease;
            position: relative;
            z-index: 2;
        }
        .step-line {
            flex: 1;
            height: 3px;
            background: #e2e8f0;
            margin: 0 4px;
            transition: all 0.3s ease;
            position: relative;
            top: -18px;
            z-index: 1;
        }
        .progress-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .progress-container::-webkit-scrollbar {
            display: none;
        }
        .progress-step {
            min-width: 90px;
            flex: 0 0 auto;
        }
        .upload-zone {
            border: 2px dashed #bfdbfe;
            transition: all 0.3s ease;
        }
        .upload-zone:hover {
            border-color: #60a5fa;
            background: #eff6ff;
        }
        .upload-zone.dragover {
            border-color: #2563eb;
            background: #dbeafe;
        }
        .house-option {
            transition: all 0.2s ease;
        }
        .house-option:hover {
            background: #f0f9ff;
        }
        .house-option.selected {
            background: #eff6ff;
            border-color: #2563eb;
        }
        .rf-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 50;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 2.5rem 1rem 1rem;
            background: rgba(15, 23, 42, 0.48);
            backdrop-filter: blur(6px);
            overflow-y: auto;
        }
        .rf-modal-backdrop.hidden {
            display: none;
        }
        .rf-modal-panel {
            width: min(100%, 44rem);
            max-height: calc(100vh - 4rem);
            overflow-y: auto;
            border-radius: 1rem;
            border: 1px solid #e2e8f0;
            background: #fff;
            box-shadow: 0 25px 70px rgba(15, 23, 42, 0.22);
        }
        .rf-details-panel {
            width: min(100%, 42rem);
        }
        .rf-modal-panel::-webkit-scrollbar {
            width: 10px;
        }
        .rf-modal-panel::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border: 3px solid #fff;
            border-radius: 999px;
        }
        @media (max-width: 640px) {
            .rf-modal-backdrop {
                align-items: stretch;
                padding: 0.75rem;
            }
            .rf-modal-panel {
                max-height: calc(100vh - 1.5rem);
                border-radius: 0.875rem;
            }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-50/30 min-h-screen font-sans text-slate-800 flex overflow-hidden">
    <?php include __DIR__ . '/../public/components/sidebar.php'; ?>
    <div class="flex-1 flex flex-col min-h-screen">
        <?php include __DIR__ . '/../public/components/header.php'; ?>
        <main class="flex-1 overflow-y-auto p-4 lg:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div><h1 class="text-2xl font-bold text-slate-900">Tenants</h1><p class="text-slate-500 mt-1">Manage your tenants</p></div>
                <button onclick="openTenantModal()" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all inline-flex items-center gap-2"><i class="fas fa-plus"></i>Add Tenant</button>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead><tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50 bg-blue-50/50">
                            <th class="px-6 py-4">Name</th><th class="px-6 py-4">Property</th><th class="px-6 py-4">Unit</th><th class="px-6 py-4">Phone</th><th class="px-6 py-4">Balance</th><th class="px-6 py-4">Lease End</th><th class="px-6 py-4">Actions</th>
                        </tr></thead>
                        <tbody class="divide-y divide-blue-50" id="tenantsTable">
                            <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- TENANT ONBOARDING WIZARD MODAL -->
    <div id="tenantModal" class="hidden rf-modal-backdrop" onclick="if(event.target===this)closeTenantModal()">
        <div class="rf-modal-panel p-5 sm:p-6 lg:p-8" onclick="event.stopPropagation()">

            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-xl font-bold text-slate-900">Add Tenant</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Step <span id="stepNum">1</span> of 5</p>
                </div>
                <button onclick="closeTenantModal()" class="text-slate-400 hover:text-slate-600 transition-colors"><i class="fas fa-times text-xl"></i></button>
            </div>

            <!-- Progress Indicator -->
            <div class="progress-container flex items-center mb-8 px-2">
                <div class="progress-step active text-center" data-step="1">
                    <div class="step-circle mx-auto">1</div>
                    <p class="text-xs font-medium mt-1.5 text-blue-600">Personal</p>
                </div>
                <div class="step-line"></div>
                <div class="progress-step text-center" data-step="2">
                    <div class="step-circle mx-auto">2</div>
                    <p class="text-xs font-medium mt-1.5 text-slate-400">ID</p>
                </div>
                <div class="step-line"></div>
                <div class="progress-step text-center" data-step="3">
                    <div class="step-circle mx-auto">3</div>
                    <p class="text-xs font-medium mt-1.5 text-slate-400">Lease</p>
                </div>
                <div class="step-line"></div>
                <div class="progress-step text-center" data-step="4">
                    <div class="step-circle mx-auto">4</div>
                    <p class="text-xs font-medium mt-1.5 text-slate-400">Finance</p>
                </div>
                <div class="step-line"></div>
                <div class="progress-step text-center" data-step="5">
                    <div class="step-circle mx-auto">5</div>
                    <p class="text-xs font-medium mt-1.5 text-slate-400">Docs</p>
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

                <!-- STEP 2: Identification -->
                <div class="step-card hidden" id="step2">
                    <div class="bg-gradient-to-br from-emerald-50 to-emerald-100/50 rounded-xl p-5 border border-emerald-100/80 shadow-sm">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white flex items-center justify-center text-lg"><i class="fas fa-id-card"></i></div>
                            <div><h4 class="font-bold text-slate-900">Identification</h4><p class="text-xs text-slate-500">Government-issued ID and emergency contact</p></div>
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
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Emergency Contact</label>
                                <div class="relative">
                                    <i class="fas fa-phone-alt absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                    <input type="text" id="tenantEmergency" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-all" placeholder="Name and phone number of emergency contact">
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-between mt-6 pt-4 border-t border-emerald-100/70">
                            <button type="button" onclick="prevStep()" class="px-5 py-2.5 bg-white text-slate-700 border border-slate-200 rounded-xl font-medium hover:bg-slate-50 transition-all inline-flex items-center gap-2"><i class="fas fa-arrow-left text-sm"></i> Back</button>
                            <button type="button" onclick="nextStep()" class="px-6 py-2.5 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-semibold rounded-xl shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/40 transition-all inline-flex items-center gap-2">Next <i class="fas fa-arrow-right text-sm"></i></button>
                        </div>
                    </div>
                </div>

                <!-- STEP 3: Lease & Assignment -->
                <div class="step-card hidden" id="step3">
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

                <!-- STEP 4: Financial Details -->
                <div class="step-card hidden" id="step4">
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
                            <button type="button" onclick="prevStep()" class="px-5 py-2.5 bg-white text-slate-700 border border-slate-200 rounded-xl font-medium hover:bg-slate-50 transition-all inline-flex items-center gap-2"><i class="fas fa-arrow-left text-sm"></i> Back</button>
                            <button type="button" onclick="nextStep()" class="px-6 py-2.5 bg-gradient-to-r from-purple-500 to-purple-600 text-white font-semibold rounded-xl shadow-lg shadow-purple-500/30 hover:shadow-purple-500/40 transition-all inline-flex items-center gap-2">Next <i class="fas fa-arrow-right text-sm"></i></button>
                        </div>
                    </div>
                </div>

                <!-- STEP 5: Documents & Review -->
                <div class="step-card hidden" id="step5">
                    <div class="bg-gradient-to-br from-rose-50 to-rose-100/50 rounded-xl p-5 border border-rose-100/80 shadow-sm">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-500 to-rose-600 text-white flex items-center justify-center text-lg"><i class="fas fa-upload"></i></div>
                            <div><h4 class="font-bold text-slate-900">Documents & Review</h4><p class="text-xs text-slate-500">Upload documents and confirm details</p></div>
                        </div>
                        <!-- Document Upload -->
                        <div class="upload-zone rounded-xl p-6 text-center cursor-pointer mb-4" id="uploadZone" onclick="document.getElementById('fileInput').click()">
                            <div class="flex flex-col items-center gap-2">
                                <div class="w-14 h-14 rounded-full bg-blue-50 flex items-center justify-center"><i class="fas fa-cloud-upload-alt text-2xl text-blue-400"></i></div>
                                <p class="text-sm font-medium text-slate-700">Drop files here or click to upload</p>
                                <p class="text-xs text-slate-400">ID, passport, lease agreement, receipts (PDF, JPG, PNG - Max 10MB)</p>
                            </div>
                            <input type="file" id="fileInput" class="hidden" accept=".pdf,.jpg,.jpeg,.png" multiple onchange="handleFileUpload(this.files)">
                        </div>
                        <div id="uploadedFiles" class="space-y-2 mb-4"></div>

                        <!-- Summary Review -->
                        <div class="bg-white/70 rounded-xl p-4 border border-rose-100/60">
                            <h5 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2"><i class="fas fa-clipboard-list text-rose-500"></i>Summary</h5>
                            <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm" id="summaryDetails">
                                <div class="text-slate-500">Name:</div><div class="font-medium text-slate-900" id="sumName">-</div>
                                <div class="text-slate-500">Email:</div><div class="font-medium text-slate-900" id="sumEmail">-</div>
                                <div class="text-slate-500">Phone:</div><div class="font-medium text-slate-900" id="sumPhone">-</div>
                                <div class="text-slate-500">ID:</div><div class="font-medium text-slate-900" id="sumId">-</div>
                                <div class="text-slate-500">Unit:</div><div class="font-medium text-slate-900" id="sumUnit">-</div>
                                <div class="text-slate-500">Rent:</div><div class="font-medium text-slate-900" id="sumRent">KES 0</div>
                                <div class="text-slate-500">Deposit:</div><div class="font-medium text-slate-900" id="sumDeposit">KES 0</div>
                                <div class="text-slate-500">Lease:</div><div class="font-medium text-slate-900" id="sumLease">-</div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-between mt-4">
                        <button type="button" onclick="prevStep()" class="px-5 py-2.5 bg-white text-slate-700 border border-slate-200 rounded-xl font-medium hover:bg-slate-50 transition-all inline-flex items-center gap-2"><i class="fas fa-arrow-left text-sm"></i> Back</button>
                        <button type="button" onclick="submitTenant()" class="px-8 py-2.5 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-semibold rounded-xl shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/40 transition-all inline-flex items-center gap-2"><i class="fas fa-check-circle text-sm"></i> Register Tenant</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- TENANT DETAILS MODAL -->
    <div id="tenantDetailsModal" class="hidden rf-modal-backdrop" onclick="if(event.target===this)closeTenantDetailsModal()">
        <div class="rf-modal-panel rf-details-panel p-5 sm:p-6 lg:p-8" onclick="event.stopPropagation()">
            <div class="flex items-start justify-between gap-4 mb-6">
                <div>
                    <h3 class="text-xl font-bold text-slate-900">Tenant details</h3>
                    <p class="text-sm text-slate-500 mt-1">Review the tenant profile and lease information.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button id="tenantEditBtn" onclick="enableTenantEdit()" class="px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 hover:bg-slate-50">Edit</button>
                    <button id="tenantSaveBtn" onclick="saveTenantEdits()" class="hidden px-3 py-1.5 bg-emerald-500 text-white rounded-xl text-sm">Save</button>
                    <button id="tenantCancelBtn" onclick="cancelTenantEdit()" class="hidden px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 hover:bg-slate-50">Cancel</button>
                    <button onclick="closeTenantDetailsModal()" class="text-slate-400 hover:text-slate-600 transition-colors ml-2"><i class="fas fa-times text-xl"></i></button>
                </div>
            </div>
            <div class="grid gap-4">
                <div class="rounded-2xl bg-slate-50 p-4">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-700 flex items-center justify-center text-lg font-bold" id="detailInitials">TN</div>
                        <div>
                            <p class="text-lg font-semibold text-slate-900" id="detailName">Tenant Name</p>
                            <input id="detailNameInput" class="hidden w-full px-3 py-2 rounded-md border border-slate-200 mt-1" />
                            <p class="text-sm text-slate-500" id="detailEmail">tenant@example.com</p>
                            <input id="detailEmailInput" class="hidden w-full px-3 py-2 rounded-md border border-slate-200 mt-1" />
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-white rounded-2xl border border-slate-200 p-4">
                        <p class="text-xs uppercase tracking-wider text-slate-400 mb-2">Property</p>
                        <p class="font-medium text-slate-900" id="detailProperty">-</p>
                        <input id="detailPropertyInput" class="hidden w-full px-3 py-2 rounded-md border border-slate-200 mt-1" />
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-200 p-4">
                        <p class="text-xs uppercase tracking-wider text-slate-400 mb-2">Unit</p>
                        <p class="font-medium text-slate-900" id="detailUnit">-</p>
                        <input id="detailUnitInput" class="hidden w-full px-3 py-2 rounded-md border border-slate-200 mt-1" />
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-200 p-4">
                        <p class="text-xs uppercase tracking-wider text-slate-400 mb-2">Phone</p>
                        <p class="font-medium text-slate-900" id="detailPhone">-</p>
                        <input id="detailPhoneInput" class="hidden w-full px-3 py-2 rounded-md border border-slate-200 mt-1" />
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-200 p-4">
                        <p class="text-xs uppercase tracking-wider text-slate-400 mb-2">Balance</p>
                        <p class="font-medium text-emerald-600" id="detailBalance">KES 0</p>
                        <input id="detailBalanceInput" type="number" class="hidden w-full px-3 py-2 rounded-md border border-slate-200 mt-1" />
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-200 p-4">
                        <p class="text-xs uppercase tracking-wider text-slate-400 mb-2">Lease end</p>
                        <p class="font-medium text-slate-900" id="detailLeaseEnd">-</p>
                        <input id="detailLeaseEndInput" type="date" class="hidden w-full px-3 py-2 rounded-md border border-slate-200 mt-1" />
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-200 p-4">
                        <p class="text-xs uppercase tracking-wider text-slate-400 mb-2">ID</p>
                        <p class="font-medium text-slate-900" id="detailId">-</p>
                        <div class="hidden" id="detailIdInputs">
                            <input id="detailIdTypeInput" class="w-full px-3 py-2 rounded-md border border-slate-200 mt-1" placeholder="ID type" />
                            <input id="detailIdNumberInput" class="w-full px-3 py-2 rounded-md border border-slate-200 mt-2" placeholder="ID number" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="toast" class="fixed bottom-6 right-6 z-50 hidden px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2"></div>

    <script>
    // ==================== CONFIG ====================
    const BASE = window.location.pathname.replace(/\/[^\/]*$/, '');
    const API = (BASE || '') + '/api';
    const token = localStorage.getItem('rf_token') || '<?php echo $token; ?>';
    const headers = token ? {'Authorization':'Bearer '+token, 'Content-Type':'application/json'} : {'Content-Type':'application/json'};
    let tenantsCache = [];
    let uploadedDocs = [];
    let currentStep = 1;
    const totalSteps = 5;

    // ==================== HELPERS ====================
    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, char => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[char]));
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

    function updateProgress(step) {
        for (let i = 1; i <= totalSteps; i++) {
            const el = document.querySelector(`.progress-step[data-step="${i}"]`);
            const circle = el.querySelector('.step-circle');
            const label = el.querySelector('p');
            if (i < step) {
                el.className = 'progress-step completed text-center';
                circle.innerHTML = '<i class="fas fa-check text-xs"></i>';
                label.className = 'text-xs font-medium mt-1.5 text-emerald-600';
            } else if (i === step) {
                el.className = 'progress-step active text-center';
                circle.textContent = i;
                const colors = ['text-blue-600','text-emerald-600','text-amber-600','text-purple-600','text-rose-600'];
                label.className = `text-xs font-medium mt-1.5 ${colors[i-1]}`;
            } else {
                el.className = 'progress-step text-center';
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
            if (!document.getElementById('tenantHouse').value) { toast('Please select a house/unit', 'error'); return false; }
            return true;
        }
        if (step === 4) {
            const rent = parseFloat(document.getElementById('tenantRent').value);
            if (!rent || rent <= 0) { toast('Please enter a valid monthly rent amount', 'error'); return false; }
            return true;
        }
        return true;
    }

    function nextStep() {
        if (!validateStep(currentStep)) return;
        // Update summary when leaving step 4
        if (currentStep === 4) updateSummary();
        showStep(currentStep + 1);
    }

    function prevStep() {
        showStep(currentStep - 1);
    }

    // ==================== LOAD TENANTS TABLE ====================
    async function loadTenants() {
        try {
            const res = await fetch(`${API}/tenants`, { headers });
            const data = await res.json();
            tenantsCache = data.tenants || [];
            const tbody = document.getElementById('tenantsTable');
            if (!res.ok) throw new Error(data.error || 'Failed to load');
            if (data.tenants && data.tenants.length) {
                tbody.innerHTML = data.tenants.map(t => `
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="px-6 py-4"><div class="flex items-center gap-2"><div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">${escapeHtml(initials(t.name))}</div><div><p class="text-sm font-medium text-slate-900">${escapeHtml(t.name || 'N/A')}</p><p class="text-xs text-slate-400">${escapeHtml(t.email || '')}</p></div></div></td>
                        <td class="px-6 py-4 text-sm text-slate-600">${escapeHtml(t.property_name || 'N/A')}</td>
                        <td class="px-6 py-4 text-sm font-medium text-slate-900">${escapeHtml(t.house_unit || 'N/A')}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">${escapeHtml(t.phone || 'N/A')}</td>
                        <td class="px-6 py-4 text-sm font-medium ${Number(t.balance) > 0 ? 'text-amber-600' : 'text-emerald-600'}">KES ${formatMoney(t.balance)}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">${escapeHtml(formatDate(t.lease_end))}</td>
                        <td class="px-6 py-4"><button onclick="openTenantDetails(${Number(t.id)||0})" class="p-1.5 text-slate-400 hover:text-blue-600 transition-colors" aria-label="View tenant details"><i class="fas fa-eye"></i></button></td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-12 text-center text-slate-400">No tenants found. Click "Add Tenant" to register one.</td></tr>';
            }
        } catch(e) {
            console.error('loadTenants error:', e);
            document.getElementById('tenantsTable').innerHTML = `<tr><td colspan="7" class="px-6 py-12 text-center text-red-400">Error: ${escapeHtml(e.message)}</td></tr>`;
            if (e.message.includes('401')) window.location.href = '/signin';
        }
    }

    // ==================== LOAD PROPERTIES ====================
    async function loadProperties() {
        try {
            const res = await fetch(`${API}/properties`, { headers });
            const data = await res.json();
            if (!res.ok) throw new Error(data.error || 'Failed');
            const select = document.getElementById('tenantProperty');
            if (data.properties && data.properties.length) {
                select.innerHTML = '<option value="">Select property...</option>' + data.properties.map(p => `<option value="${Number(p.id)||0}">${escapeHtml(p.name)} (${Number(p.units)||0} units)</option>`).join('');
            } else {
                select.innerHTML = '<option value="">No properties available</option>';
            }
        } catch(e) { console.error('loadProperties:', e); toast('Could not load properties: ' + e.message, 'error'); }
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
        try {
            const res = await fetch(`${API}/houses?property_id=${propId}&status=vacant`, { headers });
            const data = await res.json();
            if (!res.ok) throw new Error(data.error || 'Failed');
            
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
        } catch(e) { console.error(e); }
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

    // ==================== FILE UPLOAD ====================
    async function handleFileUpload(files) {
        for (const file of files) {
            const formData = new FormData();
            formData.append('file', file);
            
            // Show uploading indicator
            const container = document.getElementById('uploadedFiles');
            const uploadId = 'upload-' + Date.now();
            container.innerHTML += `<div id="${uploadId}" class="flex items-center gap-2 text-sm text-slate-500"><i class="fas fa-spinner fa-spin text-blue-400"></i> Uploading ${escapeHtml(file.name)}...</div>`;
            
            try {
                const res = await fetch(`${API}/upload`, {
                    method: 'POST',
                    headers: {'Authorization': 'Bearer ' + token},
                    body: formData
                });
                const data = await res.json();
                document.getElementById(uploadId)?.remove();
                if (!res.ok) throw new Error(data.error || 'Upload failed');
                uploadedDocs.push(data.file);
                renderUploadedFiles();
                toast(`${file.name} uploaded successfully`, 'success');
            } catch(err) {
                document.getElementById(uploadId)?.remove();
                toast(err.message, 'error');
            }
        }
    }

    // Drag and drop support
    const uploadZone = document.getElementById('uploadZone');
    uploadZone.addEventListener('dragover', (e) => { e.preventDefault(); uploadZone.classList.add('dragover'); });
    uploadZone.addEventListener('dragleave', () => { uploadZone.classList.remove('dragover'); });
    uploadZone.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadZone.classList.remove('dragover');
        if (e.dataTransfer.files.length) handleFileUpload(e.dataTransfer.files);
    });

    function renderUploadedFiles() {
        const container = document.getElementById('uploadedFiles');
        if (!uploadedDocs.length) { container.innerHTML = ''; return; }
        container.innerHTML = uploadedDocs.map((f, i) => `
            <div class="flex items-center justify-between bg-white rounded-lg px-3 py-2 border border-blue-100 shadow-sm">
                <div class="flex items-center gap-2.5 min-w-0">
                    <i class="fas ${f.type && f.type.includes('pdf') ? 'fa-file-pdf text-red-400' : 'fa-file-image text-blue-400'} text-lg"></i>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-700 truncate">${escapeHtml(f.name)}</p>
                        <p class="text-xs text-slate-400">${f.size ? (f.size/1024).toFixed(1) + ' KB' : ''}</p>
                    </div>
                </div>
                <button type="button" onclick="removeFile(${i})" class="ml-2 p-1 text-slate-400 hover:text-red-500 transition-colors flex-shrink-0"><i class="fas fa-times-circle"></i></button>
            </div>
        `).join('');
    }

    function removeFile(index) {
        uploadedDocs.splice(index, 1);
        renderUploadedFiles();
        toast('File removed', 'info');
    }

    // ==================== SUMMARY ====================
    function updateSummary() {
        document.getElementById('sumName').textContent = document.getElementById('tenantName').value || '-';
        document.getElementById('sumEmail').textContent = document.getElementById('tenantEmail').value || '-';
        document.getElementById('sumPhone').textContent = document.getElementById('tenantPhone').value || '-';
        const idType = document.getElementById('tenantIdType').value;
        const idNum = document.getElementById('tenantIdNumber').value;
        document.getElementById('sumId').textContent = idNum ? `${idType}: ${idNum}` : '-';
        
        const houseSelect = document.getElementById('tenantHouse');
        const selectedHouse = houseSelect.options[houseSelect.selectedIndex];
        document.getElementById('sumUnit').textContent = selectedHouse && selectedHouse.value ? selectedHouse.text.split(' (KES')[0] : '-';
        
        const rent = parseFloat(document.getElementById('tenantRent').value) || 0;
        const deposit = parseFloat(document.getElementById('tenantDeposit').value) || 0;
        document.getElementById('sumRent').textContent = 'KES ' + rent.toLocaleString();
        document.getElementById('sumDeposit').textContent = deposit ? 'KES ' + deposit.toLocaleString() : 'KES 0';
        
        const start = document.getElementById('tenantLeaseStart').value;
        const end = document.getElementById('tenantLeaseEnd').value;
        document.getElementById('sumLease').textContent = start ? (end ? `${start} to ${end}` : `${start} - Open`) : 'Not set';
    }

    // ==================== MODAL CONTROL ====================
    function openTenantModal() {
        currentStep = 1;
        showStep(1);
        document.getElementById('tenantModal').classList.remove('hidden');
        loadProperties();
        // Reset form
        document.getElementById('tenantForm').reset();
        uploadedDocs = [];
        renderUploadedFiles();
        document.getElementById('housePreview').classList.add('hidden');
        document.getElementById('tenantHouse').innerHTML = '<option value="">Select house...</option>';
    }
    
    function closeTenantModal() {
        document.getElementById('tenantModal').classList.add('hidden');
    }

    function openTenantDetails(id) {
        const tenant = tenantsCache.find(t => Number(t.id) === Number(id));
        if (!tenant) {
            toast('Tenant details are not available right now.', 'error');
            return;
        }
        currentTenantId = Number(id);
        cancelTenantEdit();
        document.getElementById('detailInitials').textContent = initials(tenant.name, 'TN');
        document.getElementById('detailName').textContent = tenant.name || 'N/A';
        document.getElementById('detailEmail').textContent = tenant.email || 'No email provided';
        document.getElementById('detailProperty').textContent = tenant.property_name || 'N/A';
        document.getElementById('detailUnit').textContent = tenant.house_unit || 'N/A';
        document.getElementById('detailPhone').textContent = tenant.phone || 'N/A';
        document.getElementById('detailBalance').textContent = `KES ${formatMoney(tenant.balance)}`;
        document.getElementById('detailLeaseEnd').textContent = formatDate(tenant.lease_end);
        document.getElementById('detailId').textContent = tenant.id_type ? `${tenant.id_type} • ${tenant.id_number || 'N/A'}` : (tenant.id_number || 'N/A');
        document.getElementById('tenantDetailsModal').classList.remove('hidden');
    }

    function closeTenantDetailsModal() {
        cancelTenantEdit();
        currentTenantId = null;
        document.getElementById('tenantDetailsModal').classList.add('hidden');
    }

    // ========== EDIT HANDLERS ==========
    let currentTenantId = null;
    function enableTenantEdit() {
        if (!currentTenantId) return;
        // show inputs and fill values
        const t = tenantsCache.find(x => Number(x.id) === Number(currentTenantId));
        if (!t) return;
        document.getElementById('detailNameInput').value = t.name || '';
        document.getElementById('detailEmailInput').value = t.email || '';
        document.getElementById('detailPhoneInput').value = t.phone || '';
        document.getElementById('detailBalanceInput').value = t.balance || 0;
        document.getElementById('detailLeaseEndInput').value = t.lease_end ? t.lease_end.substring(0,10) : '';
        document.getElementById('detailIdTypeInput').value = t.id_type || '';
        document.getElementById('detailIdNumberInput').value = t.id_number || '';

        // toggle visibility
        ['detailNameInput','detailEmailInput','detailPhoneInput','detailBalanceInput','detailLeaseEndInput','detailIdInputs'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.classList.remove('hidden');
        });
        // hide static displays
        ['detailName','detailEmail','detailPhone','detailBalance','detailLeaseEnd','detailId'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.classList.add('hidden');
        });
        document.getElementById('tenantEditBtn').classList.add('hidden');
        document.getElementById('tenantSaveBtn').classList.remove('hidden');
        document.getElementById('tenantCancelBtn').classList.remove('hidden');
    }

    function cancelTenantEdit() {
        // hide inputs
        ['detailNameInput','detailEmailInput','detailPhoneInput','detailBalanceInput','detailLeaseEndInput','detailIdInputs'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.classList.add('hidden');
        });
        // show static
        ['detailName','detailEmail','detailPhone','detailBalance','detailLeaseEnd','detailId'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.classList.remove('hidden');
        });
        document.getElementById('tenantEditBtn').classList.remove('hidden');
        document.getElementById('tenantSaveBtn').classList.add('hidden');
        document.getElementById('tenantCancelBtn').classList.add('hidden');
    }

    async function saveTenantEdits() {
        if (!currentTenantId) return;
        const btn = document.getElementById('tenantSaveBtn');
        btn.disabled = true;
        btn.textContent = 'Saving...';
        const payload = {
            name: document.getElementById('detailNameInput').value.trim(),
            email: document.getElementById('detailEmailInput').value.trim() || null,
            phone: document.getElementById('detailPhoneInput').value.trim() || null,
            balance: parseFloat(document.getElementById('detailBalanceInput').value) || 0,
            lease_end: document.getElementById('detailLeaseEndInput').value || null,
            id_type: document.getElementById('detailIdTypeInput').value || null,
            id_number: document.getElementById('detailIdNumberInput').value || null,
        };
        try {
            const res = await fetch(`${API}/tenants/${currentTenantId}`, {
                method: 'PUT',
                headers,
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.error || 'Failed to update');
            // update cache and UI
            const idx = tenantsCache.findIndex(x => Number(x.id) === Number(currentTenantId));
            if (idx !== -1) tenantsCache[idx] = { ...tenantsCache[idx], ...data.tenant };
            // refresh detail display
            const t = tenantsCache[idx];
            document.getElementById('detailName').textContent = t.name || 'N/A';
            document.getElementById('detailEmail').textContent = t.email || 'N/A';
            document.getElementById('detailPhone').textContent = t.phone || 'N/A';
            document.getElementById('detailBalance').textContent = `KES ${formatMoney(t.balance)}`;
            document.getElementById('detailLeaseEnd').textContent = formatDate(t.lease_end);
            document.getElementById('detailId').textContent = t.id_type ? `${t.id_type} • ${t.id_number||'N/A'}` : (t.id_number||'N/A');
            toast('Tenant updated', 'success');
            cancelTenantEdit();
            loadTenants();
        } catch (e) {
            console.error('Update tenant error:', e);
            toast(e.message || 'Could not update tenant', 'error');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Save';
        }
    }

    // ==================== SUBMIT ====================
    async function submitTenant() {
        const btn = event.target;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registering...';
        
        const houseSelect = document.getElementById('tenantHouse');
        const data = {
            name: document.getElementById('tenantName').value.trim(),
            email: document.getElementById('tenantEmail').value.trim() || null,
            phone: document.getElementById('tenantPhone').value.trim(),
            id_type: document.getElementById('tenantIdType').value,
            id_number: document.getElementById('tenantIdNumber').value.trim(),
            emergency_contact: document.getElementById('tenantEmergency').value.trim() || null,
            property_id: parseInt(document.getElementById('tenantProperty').value) || null,
            house_id: parseInt(houseSelect.value) || null,
            lease_start: document.getElementById('tenantLeaseStart').value || null,
            lease_end: document.getElementById('tenantLeaseEnd').value || null,
            rent: parseFloat(document.getElementById('tenantRent').value) || 0,
            deposit: parseFloat(document.getElementById('tenantDeposit').value) || 0,
            balance: parseFloat(document.getElementById('tenantBalance').value) || 0,
            documents: uploadedDocs.length ? JSON.stringify(uploadedDocs) : null,
        };

        // Final validation
        if (!data.name) { toast('Name is required', 'error'); btn.disabled = false; btn.innerHTML = '<i class="fas fa-check-circle text-sm"></i> Register Tenant'; return; }
        if (!data.phone) { toast('Phone is required', 'error'); btn.disabled = false; btn.innerHTML = '<i class="fas fa-check-circle text-sm"></i> Register Tenant'; return; }
        if (!data.id_number) { toast('ID number is required', 'error'); btn.disabled = false; btn.innerHTML = '<i class="fas fa-check-circle text-sm"></i> Register Tenant'; return; }
        if (!data.house_id) { toast('Please assign a house/unit', 'error'); btn.disabled = false; btn.innerHTML = '<i class="fas fa-check-circle text-sm"></i> Register Tenant'; return; }
        if (!data.rent || data.rent <= 0) { toast('Please enter a valid rent amount', 'error'); btn.disabled = false; btn.innerHTML = '<i class="fas fa-check-circle text-sm"></i> Register Tenant'; return; }

        try {
            console.log('Submitting tenant:', data);
            const res = await fetch(`${API}/tenants`, {
                method: 'POST',
                headers,
                body: JSON.stringify(data)
            });
            const text = await res.text();
            let result;
            try { result = JSON.parse(text); } catch(e) { throw new Error('Server error: ' + text.substring(0, 200)); }
            if (!res.ok) throw new Error(result.error || 'Failed to register tenant');
            
            toast('Tenant registered successfully! Unit assigned and bill generated.', 'success');
            closeTenantModal();
            loadTenants();
        } catch(err) {
            console.error('Submit tenant error:', err);
            toast(err.message, 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle text-sm"></i> Register Tenant';
        }
    }

    // ==================== INIT ====================
    loadTenants();
    </script>
</body>
</html>
