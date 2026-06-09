<?php

function get_locale(): string {
    return $_SESSION['lang'] ?? 'English';
}

function trans(string $key): string {
    static $translations = [];

    $locale = get_locale();

    if (!isset($translations[$locale])) {
        $translations[$locale] = json_decode(get_lang_json(), true) ?? [];
    }

    $keys = explode('.', $key);
    $value = $translations[$locale];
    foreach ($keys as $k) {
        $value = $value[$k] ?? null;
        if ($value === null) return $key;
    }
    return $value;
}

function get_available_languages(): array {
    $langs = [];
    foreach (glob($_SERVER['DOCUMENT_ROOT'] . '/lang/*.json') as $file) {
        $name = basename($file, '.json');
        $langs[] = ['code' => $name, 'label' => $name];
    }
    return $langs;
}

function get_lang_json(): string {
    $locale = get_locale();
    $file = $_SERVER['DOCUMENT_ROOT'] . "/lang/{$locale}.json";
    if (!file_exists($file)) {
        $file = $_SERVER['DOCUMENT_ROOT'] . "/lang/English.json";
    }
    return file_get_contents($file);
}
