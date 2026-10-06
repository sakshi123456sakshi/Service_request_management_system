<?php
require_once __DIR__ . '/../../config/bootstrap.php';

require_method('GET');
require_auth();

$stmt = getDB()->query('SELECT id, name FROM categories ORDER BY name');
json_success($stmt->fetchAll(), 'OK');
