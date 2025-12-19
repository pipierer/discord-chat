<?php

$database_url = getenv("DATABASE_URL");

if (!$database_url) {
    die("DATABASE_URL non défini");
}

$db = new PDO($database_url);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
