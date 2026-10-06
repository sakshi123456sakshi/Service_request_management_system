<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../helpers/request_helpers.php';

require_method('POST');
$user = require_auth();
$input = read_json_body();

$errors = validate($input, [
    'request_id' => 'required|numeric',
    'remark'     => 'required|max:1000',
]);
if ($errors) {
    json_error('Validation failed.', 422, $errors);
}

$db = getDB();
$request = fetch_request_or_404($db, (int)$input['request_id']);
guard_request_access($user, $request);

$stmt = $db->prepare('INSERT INTO remarks (request_id, user_id, role, remark) VALUES (?, ?, ?, ?)');
$stmt->execute([$request['id'], $user['id'], $user['role'], trim($input['remark'])]);

json_success(['id' => (int)$db->lastInsertId()], 'Remark added.', 201);
