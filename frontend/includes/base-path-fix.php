<?php
// Shared base path for all pages
// Priority: environment config > computed from script path
$basePath = rtrim((string) ($_ENV['BASE_PATH'] ?? getenv('BASE_PATH') ?? rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/')), '/');
