<?php
// Inject the HTML <base> tag so root-relative asset URLs resolve correctly
// even when the app lives in a subfolder (e.g. /RentalFlow).

// Prefer the shared base-path helper if available.
$basePath = '/';
if (file_exists(__DIR__ . '/base-path-fix.php')) {
    require_once __DIR__ . '/base-path-fix.php';
    if (function_exists('getBasePath')) {
        $basePath = getBasePath();
    }
}
if (!isset($basePath) || $basePath === '' || $basePath === '\\') {
    $basePath = '/';
}
$basePath = rtrim(str_replace('\\', '/', (string)$basePath), '/');
if ($basePath === '') {
    $basePath = '/';
}
?>
<base href="<?php echo htmlspecialchars($basePath); ?>/">
