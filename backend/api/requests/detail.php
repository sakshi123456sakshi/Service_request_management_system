<?php
require_once __DIR__ . '/../../config/bootstrap.php';

require_method('GET');
$user = require_auth();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    json_error('Request id is required.', 422);
}

$db = getDB();

$stmt = $db->prepare("
    SELECT
        sr.*, c.name AS category_name,
        u.name AS raised_by_name, u.email AS raised_by_email, u.phone AS raised_by_phone,
        ra.technician_id, tech.name AS technician_name, tech.email AS technician_email
    FROM service_requests sr
    JOIN categories c ON c.id = sr.category_id
    JOIN users u ON u.id = sr.user_id
    LEFT JOIN request_assignments ra ON ra.request_id = sr.id AND ra.is_current = 1
    LEFT JOIN users tech ON tech.id = ra.technician_id
    WHERE sr.id = ?
    LIMIT 1
");
$stmt->execute([$id]);
$request = $stmt->fetch();

if (!$request) {
    json_error('Service request not found.', 404);
}

// ---- authorization: user can only see their own, technician only if assigned ----
if ($user['role'] === 'user' && (int)$request['user_id'] !== (int)$user['id']) {
    json_error('You do not have permission to view this request.', 403);
}
if ($user['role'] === 'technician' && (int)($request['technician_id'] ?? 0) !== (int)$user['id']) {
    json_error('You do not have permission to view this request.', 403);
}

// ---- history --------------------------------------------------------------
$historyStmt = $db->prepare("
    SELECT rh.id, rh.status, rh.remarks, rh.created_at, rh.updated_by_role, u.name AS updated_by_name
    FROM request_history rh
    JOIN users u ON u.id = rh.updated_by
    WHERE rh.request_id = ?
    ORDER BY rh.created_at ASC, rh.id ASC
");
$historyStmt->execute([$id]);

// ---- remarks --------------------------------------------------------------
$remarksStmt = $db->prepare("
    SELECT r.id, r.remark, r.role, r.created_at, u.name AS author_name
    FROM remarks r
    JOIN users u ON u.id = r.user_id
    WHERE r.request_id = ?
    ORDER BY r.created_at ASC, r.id ASC
");
$remarksStmt->execute([$id]);

$request['history'] = $historyStmt->fetchAll();
$request['remarks'] = $remarksStmt->fetchAll();

json_success($request, 'OK');
