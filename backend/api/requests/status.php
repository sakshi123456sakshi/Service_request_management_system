<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../helpers/request_helpers.php';

require_method('POST');
$user = require_role(['admin', 'technician']);
$input = read_json_body();

$errors = validate($input, [
    'request_id' => 'required|numeric',
    'status'     => 'required|in:New,Assigned,In Progress,Resolved,Closed',
]);
if ($errors) {
    json_error('Validation failed.', 422, $errors);
}

$db = getDB();
$request = fetch_request_or_404($db, (int)$input['request_id']);
guard_request_access($user, $request);

$from = $request['status'];
$to   = $input['status'];

if ($from === $to) {
    json_error('Request is already in that status.', 422);
}

if (!is_valid_status_transition($from, $to, $user['role'])) {
    json_error("Cannot move a request from \"$from\" to \"$to\".", 422);
}

$remark = isset($input['remark']) ? trim($input['remark']) : null;

$db->beginTransaction();
try {
    $db->prepare('UPDATE service_requests SET status = ? WHERE id = ?')->execute([$to, $request['id']]);
    record_history($db, $request['id'], $to, $user['id'], $user['role'], $remark ?: "Status changed to $to");

    if ($remark) {
        $db->prepare('INSERT INTO remarks (request_id, user_id, role, remark) VALUES (?, ?, ?, ?)')
           ->execute([$request['id'], $user['id'], $user['role'], $remark]);
    }

    $db->commit();
} catch (Throwable $e) {
    $db->rollBack();
    error_log($e->getMessage());
    json_error('Could not update status.', 500);
}

json_success([], "Status updated to $to.");
