<?php
require_once __DIR__ . '/../../config/bootstrap.php';

require_method('POST');
$user = require_role(['user']); // only end-users raise requests

// This endpoint accepts multipart/form-data so an optional file can be attached.
$input = $_POST;

$errors = validate($input, [
    'subject'     => 'required|max:150',
    'description' => 'required|max:5000',
    'category_id' => 'required|numeric',
    'priority'    => 'required|in:Low,Medium,High,Critical',
]);
if ($errors) {
    json_error('Validation failed.', 422, $errors);
}

$db = getDB();

// category must exist
$stmt = $db->prepare('SELECT id FROM categories WHERE id = ?');
$stmt->execute([$input['category_id']]);
if (!$stmt->fetch()) {
    json_error('Invalid category selected.', 422);
}

// ---- optional attachment upload -------------------------------------------------
$attachmentPath = null;
if (!empty($_FILES['attachment']['name'])) {
    $file = $_FILES['attachment'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        json_error('File upload failed.', 422);
    }

    $allowedExt  = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'txt'];
    $maxBytes    = 5 * 1024 * 1024; // 5 MB
    $ext         = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExt, true)) {
        json_error('Unsupported file type. Allowed: ' . implode(', ', $allowedExt), 422);
    }
    if ($file['size'] > $maxBytes) {
        json_error('File is too large. Maximum size is 5MB.', 422);
    }

    $uploadDir = __DIR__ . '/../../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $safeName = bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $safeName)) {
        json_error('Could not save uploaded file.', 500);
    }
    $attachmentPath = 'uploads/' . $safeName;
}

// ---- create the request ----------------------------------------------------
$db->beginTransaction();
try {
    $stmt = $db->prepare(
        'INSERT INTO service_requests (request_code, user_id, category_id, subject, description, priority, status, attachment_path)
         VALUES (?, ?, ?, ?, ?, ?, "New", ?)'
    );
    // temporary placeholder code, patched right after we know the id
    $stmt->execute([
        'SR-TEMP',
        $user['id'],
        (int)$input['category_id'],
        clean_string($input['subject']),
        trim($input['description']),
        $input['priority'],
        $attachmentPath,
    ]);
    $id = (int)$db->lastInsertId();
    $code = 'SR-' . str_pad((string)$id, 6, '0', STR_PAD_LEFT);

    $db->prepare('UPDATE service_requests SET request_code = ? WHERE id = ?')->execute([$code, $id]);

    $db->prepare(
        'INSERT INTO request_history (request_id, status, updated_by, updated_by_role, remarks) VALUES (?, "New", ?, "user", ?)'
    )->execute([$id, $user['id'], 'Request raised by user.']);

    $db->commit();
} catch (Throwable $e) {
    $db->rollBack();
    error_log($e->getMessage());
    json_error('Could not create request.', 500);
}

json_success(['id' => $id, 'request_code' => $code], 'Service request created successfully.', 201);
