<?php
// No auth required - public page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions - RentFlow</title>
    <link rel="stylesheet" href="/css/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .terms-content { max-width: 900px; margin: 0 auto; line-height: 1.8; }
        .terms-content h2 { margin-top: 2rem; margin-bottom: 0.75rem; font-size: 1.25rem; font-weight: 700; color: #0f172a; }
        .terms-content h3 { margin-top: 1.25rem; margin-bottom: 0.5rem; font-size: 1.1rem; font-weight: 600; color: #1e293b; }
        .terms-content p { margin-bottom: 1rem; color: #334155; }
        .terms-content ul { margin-bottom: 1rem; padding-left: 1.5rem; }
        .terms-content ul li { margin-bottom: 0.5rem; color: #334155; }
        .back-link { display: inline-flex; align-items: center; gap: 0.5rem; color: #2563eb; font-weight: 500; text-decoration: none; transition: color 0.2s; }
        .back-link:hover { color: #1d4ed8; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-50/30 min-h-screen font-sans text-slate-800">
    <div class="max-w-4xl mx-auto px-4 py-10">
        <a href="/signin" class="back-link mb-6 inline-flex">
            <i class="fas fa-arrow-left"></i> Back to Login
        </a>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6 md:p-10">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center">
                    <i class="fas fa-file-contract text-blue-600"></i>
                </div>
                <h1 class="text-2xl font-bold text-slate-900">Terms and Conditions</h1>
            </div>
            <p class="text-sm text-slate-500 mb-6">Last updated: June 2025</p>

            <div class="terms-content">
                <h2>1. Introduction</h2>
                <p>Welcome to <strong>RentFlow</strong>. These Terms and Conditions govern your use of the RentFlow property management platform. By accessing or using RentFlow, you agree to be bound by these terms. If you do not agree with any part of these terms, you may not use the service.</p>

                <h2>2. Definitions</h2>
                <ul>
                    <li><strong>"Service"</strong> means the RentFlow web application and related services.</li>
                    <li><strong>"Owner"</strong> means a registered property owner using the Service.</li>
                    <li><strong>"Tenant"</strong> means a registered occupant of a property managed through the Service.</li>
                    <li><strong>"Caretaker"</strong> means an authorized individual managing properties on behalf of an Owner.</li>
                </ul>

                <h2>3. User Accounts</h2>
                <p>You are responsible for maintaining the confidentiality of your account and password. You agree to accept responsibility for all activities that occur under your account.</p>

                <h2>4. Data Protection and Privacy</h2>
                <p>RentFlow is committed to protecting your personal data in accordance with applicable data protection laws. We implement appropriate technical and organizational measures to safeguard your information.</p>
                <ul>
                    <li>We collect and process personal data solely for the purpose of providing property management services.</li>
                    <li>Your data is not shared with third parties without your consent, except as required by law.</li>
                    <li>You have the right to request access to, correction of, or deletion of your personal data.</li>
                    <li>We retain your data only for as long as necessary to fulfill the purposes for which it was collected.</li>
                </ul>

                <h2>5. Acceptable Use</h2>
                <p>You agree not to use the Service for any unlawful purpose, or in any way that could damage, disable, or impair the Service or interfere with any other party's use of the Service.</p>

                <h2>6. Payments and Billing</h2>
                <p>All payment transactions are processed securely. Owners are responsible for verifying the accuracy of all payment records. RentFlow is not a party to payment agreements between Owners and Tenants.</p>

                <h2>7. Termination</h2>
                <p>We reserve the right to suspend or terminate your access to the Service at our sole discretion, without notice, for conduct that we believe violates these Terms or is harmful to other users, us, or third parties.</p>

                <h2>8. Limitation of Liability</h2>
                <p>RentFlow shall not be liable for any indirect, incidental, special, consequential, or punitive damages resulting from your use of or inability to use the Service.</p>

                <h2>9. Changes to Terms</h2>
                <p>We reserve the right to update these Terms at any time. Continued use of the Service after changes constitutes acceptance of the new Terms.</p>

                <h2>10. Contact Us</h2>
                <p>If you have any questions about these Terms and Conditions, please contact us at <a href="mailto:support@rentflow.com" class="text-blue-600 hover:underline">support@rentflow.com</a>.</p>
            </div>
        </div>
    </div>
</body>
</html>