<?php
/**
 * Common bootstrap included by every API endpoint:
 * - starts the session (cookie-based auth)
 * - sends CORS headers so the Vite dev server (or any configured origin) can call the API
 * - sets JSON response headers
 * - wires a global error/exception handler so nothing leaks raw PHP errors
 */

// ---- CORS -------------------------------------------------------------
// Allow the frontend origin(s). Add your production frontend URL here too.
$allowedOrigins = [
    'http://localhost:5173',
    'http://127.0.0.1:5173',
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
}
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// Preflight requests end here
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ---- Session ------------------------------------------------------------
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'samesite' => 'Lax',
        'httponly' => true,
        'secure'   => false, // set true when served over HTTPS in production
    ]);
    session_start();
}

// ---- Error handling -------------------------------------------------------
error_reporting(E_ALL);
ini_set('display_errors', '0'); // never leak raw errors to the client

set_exception_handler(function (Throwable $e) {
    error_log('[Unhandled] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error.']);
    exit;
});

set_error_handler(function ($severity, $message, $file, $line) {
    error_log("[PHP Error] $message in $file:$line");
    return true; // don't execute the internal PHP error handler
});

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/validate.php';
require_once __DIR__ . '/../helpers/auth.php';
