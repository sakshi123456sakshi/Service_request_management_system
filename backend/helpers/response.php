<?php
/** Small helpers for consistent JSON responses. */

function json_success($data = [], string $message = 'OK', int $code = 200): void
{
    http_response_code($code);
    echo json_encode(['success' => true, 'message' => $message, 'data' => $data]);
    exit;
}

function json_error(string $message, int $code = 400, $errors = null): void
{
    http_response_code($code);
    $payload = ['success' => false, 'message' => $message];
    if ($errors !== null) {
        $payload['errors'] = $errors;
    }
    echo json_encode($payload);
    exit;
}

/** Reads and JSON-decodes the raw request body (works for POST/PUT JSON payloads). */
function read_json_body(): array
{
    $raw = file_get_contents('php://input');
    if (!$raw) {
        return [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function method(): string
{
    return $_SERVER['REQUEST_METHOD'] ?? 'GET';
}

function require_method(string $expected): void
{
    if (method() !== $expected) {
        json_error('Method not allowed.', 405);
    }
}
