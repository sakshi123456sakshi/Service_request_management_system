<?php
require_once __DIR__ . '/../../config/bootstrap.php';

require_method('GET');
$user = require_auth();

$where  = [];
$params = [];

// ---- role-based scoping -----------------------------------------------------
if ($user['role'] === 'user') {
    $where[] = 'sr.user_id = ?';
    $params[] = $user['id'];
} elseif ($user['role'] === 'technician') {
    $where[] = 'ra.technician_id = ? AND ra.is_current = 1';
    $params[] = $user['id'];
}
// admin sees everything, no extra scoping

// ---- filters ------------------------------------------------------------
if (!empty($_GET['status'])) {
    $where[] = 'sr.status = ?';
    $params[] = $_GET['status'];
}
if (!empty($_GET['priority'])) {
    $where[] = 'sr.priority = ?';
    $params[] = $_GET['priority'];
}
if (!empty($_GET['category_id'])) {
    $where[] = 'sr.category_id = ?';
    $params[] = $_GET['category_id'];
}
if (!empty($_GET['q'])) {
    $where[] = '(sr.subject LIKE ? OR sr.request_code LIKE ? OR sr.description LIKE ?)';
    $like = '%' . $_GET['q'] . '%';
    array_push($params, $like, $like, $like);
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$sql = "
    SELECT
        sr.id, sr.request_code, sr.subject, sr.priority, sr.status, sr.created_at, sr.updated_at,
        c.name AS category_name,
        u.name AS raised_by_name, u.email AS raised_by_email,
        tech.name AS technician_name
    FROM service_requests sr
    JOIN categories c ON c.id = sr.category_id
    JOIN users u ON u.id = sr.user_id
    LEFT JOIN request_assignments ra ON ra.request_id = sr.id AND ra.is_current = 1
    LEFT JOIN users tech ON tech.id = ra.technician_id
    $whereSql
    ORDER BY
        FIELD(sr.priority, 'Critical', 'High', 'Medium', 'Low'),
        sr.created_at DESC
";

$stmt = getDB()->prepare($sql);
$stmt->execute($params);

json_success($stmt->fetchAll(), 'OK');
