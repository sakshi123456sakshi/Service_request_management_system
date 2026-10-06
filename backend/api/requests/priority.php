<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../helpers/request_helpers.php';

require_method('POST');
$admin = require_role(['admin']);
$input = read_json_body();

$errors = validate($input, [
    'request_id' => 'required|numeric',
    'priority'   => 'required|in:Low,Medium,High,Critical',
]);
if ($errors) {
    json_error('Validation failed.', 422, $errors);
}

$db = getDB();
$request = fetch_request_or_404($db, (int)$input['request_id']);

if ($request['status'] === 'Closed') {
    json_error('Cannot change priority of a closed request.', 422);
}

$db->prepare('UPDATE service_requests SET priority = ? WHERE id = ?')
   ->execute([$input['priority'], $request['id']]);

record_history(
    $db,
    $request['id'],
    $request['status'],
    $admin['id'],
    'admin',
    'Priority changed from ' . $request['priority'] . ' to ' . $input['priority']
);

json_success([], 'Priority updated.');
