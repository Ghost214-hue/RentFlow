<?php
// Inject cache-busting headers and dynamic base path into HTML <head>
// Usage: include this file in every page's <head> section

// Compute base path (go up from pages/ or public/ directories to site root)
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$basePath = rtrim(str_replace('\\', '/', $scriptDir), '/');

// If we're in a subdirectory like /RentFlow/pages or /RentFlow/frontend/public,
// $basePath already contains the correct /RentFlow prefix
?>
<!-- base-path injected by head-inject.php -->
<base href="<?php echo htmlspecialchars($basePath); ?>/">
</parameter>
<parameter>false</parameter>
<parameter>false</parameter>
<parameter>false</parameter>
</write_to_file>