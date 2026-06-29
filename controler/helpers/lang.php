<?php

function get_locale(): string
{
    return $_SESSION['lang'] ?? 'English';
}

function trans(string $key): string
{
    // Same lookup as trans_locale, just for the user's current session language
    return trans_locale(get_locale(), $key);
}

function trans_locale(string $locale, string $key): string
{
    static $translations = [];

    if (!isset($translations[$locale])) {
        $translations[$locale] = json_decode(file_get_contents(get_lang_file($locale)), true) ?? [];
    }

    $value = $translations[$locale];
    foreach (explode('.', $key) as $k) {
        $value = $value[$k] ?? null;
        if ($value === null) return $key;
    }
    return $value;
}

function get_available_language_codes(): array
{
    return array_map(
        fn($f) => basename($f, '.json'),
        glob($_SERVER['DOCUMENT_ROOT'] . '/lang/*.json')
    );
}

function get_lang_file(string $locale): string
{
    $file = $_SERVER['DOCUMENT_ROOT'] . "/lang/{$locale}.json";
    if (!file_exists($file)) {
        $file = $_SERVER['DOCUMENT_ROOT'] . "/lang/English.json";
    }
    return $file;
}

function get_lang_json(): string
{
    return file_get_contents(get_lang_file(get_locale()));
}
