<?php
/** Helpers shared by the requests/* endpoints. */

/** Fetch a bare request row (with current technician id) or abort with 404. */
function fetch_request_or_404(PDO $db, int $id): array
{
    $stmt = $db->prepare("
        SELECT sr.*, ra.technician_id
        FROM service_requests sr
        LEFT JOIN request_assignments ra ON ra.request_id = sr.id AND ra.is_current = 1
        WHERE sr.id = ?
        LIMIT 1
    ");
    $stmt->execute([$id]);
    $request = $stmt->fetch();
    if (!$request) {
        json_error('Service request not found.', 404);
    }
    return $request;
}

/** Aborts with 403 unless $user may view/act on $request (per their role). */
function guard_request_access(array $user, array $request): void
{
    if ($user['role'] === 'user' && (int)$request['user_id'] !== (int)$user['id']) {
        json_error('You do not have permission to access this request.', 403);
    }
    if ($user['role'] === 'technician' && (int)($request['technician_id'] ?? 0) !== (int)$user['id']) {
        json_error('You do not have permission to access this request.', 403);
    }
}

/**
 * Status flow: New -> Assigned -> In Progress -> Resolved -> Closed
 * Returns true if $from -> $to is a legal transition for $role.
 */
function is_valid_status_transition(string $from, string $to, string $role): bool
{
    $allowedByRole = [
        'admin' => [
            'New'         => ['Assigned'],
            'Assigned'    => ['In Progress', 'Assigned'], // reassigning keeps status
            'In Progress' => ['Resolved'],
            'Resolved'    => ['Closed', 'In Progress'],   // admin can reopen a resolved item
        ],
        'technician' => [
            'Assigned'    => ['In Progress'],
            'In Progress' => ['Resolved'],
        ],
    ];

    return isset($allowedByRole[$role][$from]) && in_array($to, $allowedByRole[$role][$from], true);
}

function record_history(PDO $db, int $requestId, string $status, int $userId, string $role, ?string $remarks = null): void
{
    $db->prepare(
        'INSERT INTO request_history (request_id, status, updated_by, updated_by_role, remarks) VALUES (?, ?, ?, ?, ?)'
    )->execute([$requestId, $status, $userId, $role, $remarks]);
}
