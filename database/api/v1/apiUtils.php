<?php

/**
 * @param array $body
 * @param array $keys
 * @param array $positiveInt
 * @return array
 */
function checkRequiredArg(array $body, array $keys)
{
    $missingKeys = [];

    foreach ($keys as $key) {
        if (!array_key_exists($key, $body)) {
            $missingKeys[] = $key;
        } else if ($body[$key] === null || $body[$key] === '') {
            $missingKeys[] = $key;
        }
    }

    if (!empty($missingKeys)) {
        sendAPIResponse(422, implode(', ', $missingKeys) . ' args missing', []);
    }

    return $body;
}

/**
 * @param array $body
 * @return array
 */
function sanitize_body(array $body): array
{
    $result = [];
    foreach ($body as $key => $value) {
        if ($value === null) {
            $result[$key] = null;
            continue;
        }
        if ($key === 'icon') {
            // Base64 image data URI: keep as-is, just raise the length cap well above the default 2000 chars
            $result[$key] = is_string($value) ? sanitize_string($value, 5000000) : false;
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

function sanitize_string(mixed $value, int $maxLength = 2000): string|false
{
    if (!is_string($value)) return false;
    $value = trim($value);
    $value = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    if (strlen($value) > $maxLength) return substr($value, 0, $maxLength);
    return $value;
}

function sanitize_int(mixed $value): int
{
    if (!is_numeric($value)) return 0;
    return (int)$value;
}

function sanitize_float(mixed $value): float|false
{
    if (!is_numeric($value)) return false;
    return (float)$value;
}

function sanitize_date(mixed $value): string
{
    if (!is_string($value)) return false;
    $d = DateTime::createFromFormat('Y-m-d', $value);
    if (!$d || $d->format('Y-m-d') !== $value) sendAPIResponse(400, 'Invalid date format (expected Y-m-d) - [' . $value . ']', []);
    return $value;
}

function sendAPIResponse(int $code, string $message, array $data = [], bool $exit = true): void
{
    http_response_code($code);
    echo json_encode(['code' => $code, 'message' => $message, 'data' => $data]);
    if ($exit) {
        exit;
    }
}
