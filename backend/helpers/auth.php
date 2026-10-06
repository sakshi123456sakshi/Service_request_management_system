<?php
/**
 * Session-based authentication helpers.
 * On login we store the user id + role in $_SESSION (httpOnly cookie),
 * so every subsequent request is authenticated automatically by the browser.
 */

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    $stmt = getDB()->prepare('SELECT id, name, email, role, phone, is_active FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user || (int)$user['is_active'] !== 1) {
        return null;
    }
    $cached = $user;
    return $user;
}

/** Aborts the request with 401 if nobody is logged in. Returns the user array. */
function require_auth(): array
{
    $user = current_user();
    if (!$user) {
        json_error('Authentication required. Please log in.', 401);
    }
    return $user;
}

/** Aborts with 403 if the logged-in user's role isn't in $roles. */
function require_role(array $roles): array
{
    $user = require_auth();
    if (!in_array($user['role'], $roles, true)) {
        json_error('You do not have permission to perform this action.', 403);
    }
    return $user;
}

function login_user(array $user): void
{
    session_regenerate_id(true); // prevent session fixation
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role']    = $user['role'];
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
