<?php

return [
    // Two-factor is mandatory for platform admins. Only switch off on a local
    // dev machine: ADMIN_REQUIRE_2FA=false
    'require_2fa' => filter_var(env('ADMIN_REQUIRE_2FA', true), FILTER_VALIDATE_BOOL),

    // TOTP tolerance in 30-second steps either side (2 = +/-60s) for clock drift.
    'totp_window' => (int) env('ADMIN_TOTP_WINDOW', 2),
];
