<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../helpers/request_helpers.php';

require_method('POST');
$admin = require_role(['admin']);
$input = read_json_body();

$errors = validate($input, [
    'request_id'    => 'required|numeric',
    'technician_id' => 'required|numeric',
]);
if ($errors) {
    json_error('Validation failed.', 422, $errors);
}

$db = getDB();
$request = fetch_request_or_404($db, (int)$input['request_id']);

if ($request['status'] === 'Closed') {
    json_error('Cannot reassign a closed request.', 422);
}

$stmt = $db->prepare("SELECT id, name FROM users WHERE id = ? AND role = 'technician' AND is_active = 1");
$stmt->execute([$input['technician_id']]);
$technician = $stmt->fetch();
if (!$technician) {
    json_error('Selected technician not found or inactive.', 422);
}

$newStatus = $request['status'] === 'New' ? 'Assigned' : $request['status'];

$db->beginTransaction();
try {
    // retire any current assignment
    $db->prepare('UPDATE request_assignments SET is_current = 0 WHERE request_id = ? AND is_current = 1')
       ->execute([$request['id']]);

    $db->prepare(
        'INSERT INTO request_assignments (request_id, technician_id, assigned_by, is_current) VALUES (?, ?, ?, 1)'
    )->execute([$request['id'], $technician['id'], $admin['id']]);

    $db->prepare('UPDATE service_requests SET status = ? WHERE id = ?')->execute([$newStatus, $request['id']]);

    record_history(
        $db,
        $request['id'],
        $newStatus,
        $admin['id'],
        'admin',
        'Assigned to technician: ' . $technician['name']
    );

    $db->commit();
} catch (Throwable $e) {
    $db->rollBack();
    error_log($e->getMessage());
    json_error('Could not assign technician.', 500);
}

json_success([], 'Request assigned to ' . $technician['name'] . '.');
