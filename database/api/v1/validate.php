<?php

/**
 * @param array $body
 * @param array $keys
 * @param array $positiveInt
 * @return array
 */
function checkRequiredArg(array $body, array $keys, array $positiveInt = []): array {
    $parameters = [];
    foreach ($keys as $key) {
        $parameters[$key] = $body[$key] ?? null;
    }

    $missings = [];
    foreach ($parameters as $key => $value) {
        if (!$value && $value !== 0 && $value !== 0.0 && $value !== "0") {
            $missings[] = $key;
        } elseif (in_array($key, $positiveInt) && (!is_numeric($value) || (int)$value <= 0)) {
            $missings[] = $key;
        }
    }
    if (count($missings) > 0) {
        http_response_code(422);
        echo json_encode(['code' => 422, 'message' => implode(', ', $missings) . ' args missing', 'data' => []]);
        exit;
    }
    return $parameters;
}

/**
 * @param array $body
 * @return array
 */
function sanitize_body(array $body): array {
    $result = [];
    foreach ($body as $key => $value) {
        if ($value === null) {
            $result[$key] = null;
            continue;
        }
        if (is_int($value) || (is_numeric($value) && !str_contains((string)$value, '.'))) {
            $result[$key] = sanitize_int($value);
        } elseif (is_float($value) || (is_numeric($value) && str_contains((string)$value, '.'))) {
            $result[$key] = sanitize_float($value);
        } else {
            $result[$key] = sanitize_string($value);
        }
    }
    return $result;
}

function sanitize_string(mixed $value, int $maxLength = 2000): string|false {
    if (!is_string($value)) return false;
    $value = trim($value);
    $value = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    if (strlen($value) > $maxLength) return false;
    return $value;
}

function sanitize_int(mixed $value): int|false {
    if (!is_numeric($value)) return false;
    return (int)$value;
}

function sanitize_float(mixed $value): float|false {
    if (!is_numeric($value)) return false;
    return (float)$value;
}

function sanitize_date(mixed $value): string|false {
    if (!is_string($value)) return false;
    $d = DateTime::createFromFormat('Y-m-d', $value);
    if (!$d || $d->format('Y-m-d') !== $value) return false;
    return $value;
}
