<?php
require_once __DIR__ . '/../../config/bootstrap.php';

require_method('POST');
$input = read_json_body();

$errors = validate($input, [
    'name'     => 'required|max:120',
    'email'    => 'required|email|max:150',
    'password' => 'required|min:8',
]);

if (!empty($input['password']) && !is_strong_password($input['password'])) {
    $errors[] = 'Password must be at least 8 characters and include a letter and a number.';
}

if ($errors) {
    json_error('Validation failed.', 422, $errors);
}

$name  = clean_string($input['name']);
$email = strtolower(trim($input['email']));
$phone = isset($input['phone']) ? clean_string($input['phone']) : null;

$db = getDB();

// Public registration always creates a "user" role account.
// Admin/technician accounts are provisioned by an existing admin (see api/admin users) or seeded directly.
$stmt = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    json_error('An account with this email already exists.', 409);
}

$hash = password_hash($input['password'], PASSWORD_BCRYPT);

$stmt = $db->prepare('INSERT INTO users (name, email, password_hash, role, phone) VALUES (?, ?, ?, "user", ?)');
$stmt->execute([$name, $email, $hash, $phone]);

json_success(['id' => (int)$db->lastInsertId()], 'Registration successful. Please log in.', 201);
