<?php
// Root-level index.php shim for setups where document root is not /public.
// It serves files from /public when they exist, otherwise forwards to Laravel.

$publicRoot = realpath(__DIR__ . '/public');
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$candidatePath = realpath($publicRoot . $requestPath);

if (
    $candidatePath !== false
    && str_starts_with($candidatePath, $publicRoot)
    && is_file($candidatePath)
) {
    $mimeType = mime_content_type($candidatePath) ?: 'application/octet-stream';
    header('Content-Type: ' . $mimeType);
    header('Content-Length: ' . (string) filesize($candidatePath));
    readfile($candidatePath);

    return;
}

require __DIR__ . '/public/index.php';
