<?php
require_once __DIR__ . '/../../config/bootstrap.php';

require_method('GET');
require_role(['admin']);

$stmt = getDB()->query(
    "SELECT id, name, email, phone FROM users WHERE role = 'technician' AND is_active = 1 ORDER BY name"
);
json_success($stmt->fetchAll(), 'OK');
