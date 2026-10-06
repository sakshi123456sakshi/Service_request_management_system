<?php
require_once __DIR__ . '/../../config/bootstrap.php';

require_method('POST');
$input = read_json_body();

$errors = validate($input, [
    'email'    => 'required|email',
    'password' => 'required',
]);
if ($errors) {
    json_error('Validation failed.', 422, $errors);
}

$email = strtolower(trim($input['email']));

$db = getDB();
$stmt = $db->prepare('SELECT id, name, email, password_hash, role, phone, is_active FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

// Generic message on purpose: never reveal whether the email exists.
if (!$user || !password_verify($input['password'], $user['password_hash'])) {
    json_error('Invalid email or password.', 401);
}

if ((int)$user['is_active'] !== 1) {
    json_error('This account has been deactivated. Contact the administrator.', 403);
}

login_user($user);
unset($user['password_hash']);

json_success($user, 'Login successful.');
