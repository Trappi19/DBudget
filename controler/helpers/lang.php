<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/controler/helpers/auth.php');

/**
 * Current user's { username, lang } read from the database, cached per request.
 * Returns null when the request is not authenticated.
 *
 * @return array{username: string, lang: string}|null
 */
function current_user_record(): ?array
{
    static $record = null;
    static $loaded = false;
    if ($loaded) {
        return $record;
    }
    $loaded = true;

    $email = Auth::email();
    if ($email === null) {
        return $record = null;
    }

    global $db;
    $query = $db->prepare('SELECT username, lang FROM user WHERE email = :email');
    $query->execute(['email' => $email]);
    return $record = ($query->fetch(PDO::FETCH_ASSOC) ?: null);
}

function current_username(): string
{
    return current_user_record()['username'] ?? '';
}

function get_locale(): string
{
    $record = current_user_record();
    if ($record !== null && !empty($record['lang'])) {
        return $record['lang'];
    }

    // Anonymous request: honour a language cookie if it names an available language.
    $cookieLang = $_COOKIE['lang'] ?? null;
    if ($cookieLang !== null) {
        $codes = array_map(fn($l) => $l['code'], get_available_languages());
        if (in_array($cookieLang, $codes, true)) {
            return $cookieLang;
        }
    }

    return 'English';
}

function trans(string $key): string
{
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

function get_available_languages(): array
{
    $langs = [];
    foreach (glob($_SERVER['DOCUMENT_ROOT'] . '/lang/*.json') as $file) {
        $name = basename($file, '.json');
        $langs[] = ['code' => $name, 'label' => $name];
    }
    return $langs;
}

function get_lang_json(): string
{
    $locale = get_locale();
    $file = $_SERVER['DOCUMENT_ROOT'] . "/lang/{$locale}.json";
    if (!file_exists($file)) {
        $file = $_SERVER['DOCUMENT_ROOT'] . "/lang/English.json";
    }
    return file_get_contents($file);
}
