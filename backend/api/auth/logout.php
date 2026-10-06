<?php
require_once __DIR__ . '/../../config/bootstrap.php';

require_method('POST');
require_auth();
logout_user();
json_success([], 'Logged out successfully.');
