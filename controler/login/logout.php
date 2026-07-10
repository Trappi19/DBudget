<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/controler/helpers/auth.php');

Auth::clear();
header("Location: /app/login");
exit();
