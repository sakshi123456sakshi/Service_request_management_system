<?php
require_once __DIR__ . '/../../config/bootstrap.php';

require_method('GET');
$user = current_user();

if (!$user) {
    json_error('Not authenticated.', 401);
}

json_success($user, 'OK');
