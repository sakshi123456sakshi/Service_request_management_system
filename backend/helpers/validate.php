<?php
/** Lightweight server-side validation helpers (no external dependency). */

/**
 * @param array $data       associative input array
 * @param array $rules      ['field' => 'required|email'] etc. Supported: required, email, min:N, max:N, in:a,b,c, numeric
 * @return array             list of human-readable error messages (empty = valid)
 */
function validate(array $data, array $rules): array
{
    $errors = [];
    foreach ($rules as $field => $ruleString) {
        $value = $data[$field] ?? null;
        $ruleList = explode('|', $ruleString);

        foreach ($ruleList as $rule) {
            [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);

            switch ($name) {
                case 'required':
                    if ($value === null || (is_string($value) && trim($value) === '')) {
                        $errors[] = ucfirst($field) . ' is required.';
                    }
                    break;
                case 'email':
                    if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = 'Please provide a valid email address.';
                    }
                    break;
                case 'min':
                    if ($value !== null && is_string($value) && strlen($value) < (int)$param) {
                        $errors[] = ucfirst($field) . " must be at least {$param} characters.";
                    }
                    break;
                case 'max':
                    if ($value !== null && is_string($value) && strlen($value) > (int)$param) {
                        $errors[] = ucfirst($field) . " must not exceed {$param} characters.";
                    }
                    break;
                case 'in':
                    $allowed = explode(',', $param);
                    if ($value !== null && $value !== '' && !in_array($value, $allowed, true)) {
                        $errors[] = ucfirst($field) . ' must be one of: ' . implode(', ', $allowed) . '.';
                    }
                    break;
                case 'numeric':
                    if ($value !== null && $value !== '' && !is_numeric($value)) {
                        $errors[] = ucfirst($field) . ' must be numeric.';
                    }
                    break;
            }
        }
    }
    return $errors;
}

/** Strong-ish password rule: min 8 chars, at least 1 letter and 1 number. */
function is_strong_password(string $password): bool
{
    return strlen($password) >= 8
        && preg_match('/[A-Za-z]/', $password)
        && preg_match('/[0-9]/', $password);
}

function clean_string(?string $s): string
{
    return trim(htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'));
}
