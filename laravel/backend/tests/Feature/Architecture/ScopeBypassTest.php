<?php

declare(strict_types=1);

use App\Support\CrossTenant;

/**
 * Architecture test: raw scope bypasses must only appear inside the
 * CrossTenant allow-list.
 *
 * Scans all PHP files under app/ for calls to withoutGlobalScopes() or
 * withoutOwnerScope() that are NOT inside CrossTenant::run() or the
 * approved caller list.
 */
it('confines raw scope bypasses to CrossTenant allow-list', function (): void {
    $appDir = app_path();
    $files = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($appDir, \RecursiveDirectoryIterator::SKIP_DOTS),
        \RecursiveIteratorIterator::LEAVES_ONLY
    );

    $allowedCallers = CrossTenant::ALLOWED_CALLERS;
    $violations = [];

    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $path = $file->getPathname();
        $relative = str_replace($appDir . DIRECTORY_SEPARATOR, '', $path);
        $content = file_get_contents($path);

        if (! preg_match('/without(GlobalScopes|OwnerScope)\s*\(/', $content)) {
            continue;
        }

        // Check if the file is in the allowed callers list
        $isAllowed = false;
        foreach ($allowedCallers as $allowed) {
            $allowedClass = str_replace('\\', DIRECTORY_SEPARATOR, substr($allowed, strrpos($allowed, '\\') + 1)) . '.php';
            if (str_ends_with($relative, $allowedClass)) {
                $isAllowed = true;
                break;
            }
        }

        if (! $isAllowed) {
            // Count occurrences
            preg_match_all('/without(GlobalScopes|OwnerScope)\s*\(/', $content, $matches);
            $violations[] = $relative . ' (' . count($matches[0]) . ' occurrence(s))';
        }
    }

    expect($violations)->toBeEmpty();
});
