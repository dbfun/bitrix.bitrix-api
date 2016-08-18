<?php

// API с попыткой сделать REST

$_SERVER["DOCUMENT_ROOT"] = dirname(__DIR__);

define("LANGUAGE_ID", "pa");
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

require($_SERVER["DOCUMENT_ROOT"]."/api/lib/index.php"); // dependencies
global $APPLICATION;
$APPLICATION->RestartBuffer();


$BAApiRouter = new BAApiRouter();
$BAApiRouter->run();