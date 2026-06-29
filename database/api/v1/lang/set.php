<?php

if ($method !== 'POST') {
    sendAPIResponse(405, 'Method not allowed', []);
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/controler/helpers/lang.php');

checkRequiredArg($body, ['lang']);

$lang = htmlspecialchars_decode($body['lang'], ENT_QUOTES);

if (!in_array($lang, get_available_language_codes(), true)) {
    sendAPIResponse(422, 'Unknown language', []);
}

$_SESSION['lang'] = $lang;

sendAPIResponse(200, 'Language updated', [
    'lang'         => $lang,
    'translations' => json_decode(get_lang_json(), true),
]);
