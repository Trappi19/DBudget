<?php

function get_locale(): string {
    return $_SESSION['lang'] ?? 'Français';
}

function t(string $key): string {
    static $translations = null;

    if ($translations === null) {
        $locale = get_locale();
        $file = $_SERVER['DOCUMENT_ROOT'] . "/lang/{$locale}.json";
        if (!file_exists($file)) {
            $file = $_SERVER['DOCUMENT_ROOT'] . "/lang/Français.json";
        }
        $translations = json_decode(file_get_contents($file), true) ?? [];
    }

    // Supporte les clés imbriquées type "nav.overview"
    $keys = explode('.', $key);
    $value = $translations;
    foreach ($keys as $k) {
        $value = $value[$k] ?? null;
        if ($value === null) return $key;
    }
    return $value;
}

function get_lang_json(): string {
    $locale = get_locale();
    $file = $_SERVER['DOCUMENT_ROOT'] . "/lang/{$locale}.json";
    if (!file_exists($file)) {
        $file = $_SERVER['DOCUMENT_ROOT'] . "/lang/Français.json";
    }
    return file_get_contents($file);
}
