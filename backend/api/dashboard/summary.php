<?php
require_once __DIR__ . '/../../config/bootstrap.php';

require_method('GET');
$user = require_auth();

$where  = [];
$params = [];

if ($user['role'] === 'user') {
    $where[] = 'sr.user_id = ?';
    $params[] = $user['id'];
} elseif ($user['role'] === 'technician') {
    $where[] = 'ra.technician_id = ? AND ra.is_current = 1';
    $params[] = $user['id'];
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$db = getDB();

$countsStmt = $db->prepare("
    SELECT sr.status, COUNT(*) AS total
    FROM service_requests sr
    LEFT JOIN request_assignments ra ON ra.request_id = sr.id AND ra.is_current = 1
    $whereSql
    GROUP BY sr.status
");
$countsStmt->execute($params);

$counts = ['New' => 0, 'Assigned' => 0, 'In Progress' => 0, 'Resolved' => 0, 'Closed' => 0];
foreach ($countsStmt->fetchAll() as $row) {
    $counts[$row['status']] = (int)$row['total'];
}
$total = array_sum($counts);

$criticalStmt = $db->prepare("
    SELECT COUNT(*) AS total
    FROM service_requests sr
    LEFT JOIN request_assignments ra ON ra.request_id = sr.id AND ra.is_current = 1
    $whereSql " . ($where ? ' AND ' : ' WHERE ') . " sr.priority = 'Critical'
");
$criticalStmt->execute($params);
$critical = (int)$criticalStmt->fetch()['total'];

$categoryStmt = $db->prepare("
    SELECT c.name AS category, COUNT(*) AS total
    FROM service_requests sr
    JOIN categories c ON c.id = sr.category_id
    LEFT JOIN request_assignments ra ON ra.request_id = sr.id AND ra.is_current = 1
    $whereSql
    GROUP BY c.name
    ORDER BY total DESC
");
$categoryStmt->execute($params);

json_success([
    'total_requests'    => $total,
    'new'                => $counts['New'],
    'assigned'           => $counts['Assigned'],
    'in_progress'        => $counts['In Progress'],
    'resolved'           => $counts['Resolved'],
    'closed'             => $counts['Closed'],
    'critical'           => $critical,
    'by_category'        => $categoryStmt->fetchAll(),
], 'OK');
