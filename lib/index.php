<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

CModule::AddAutoloadClasses('',
  array(
    'BAApiDI' => '/api/lib/BAApiDI.php',
    'BAApiLogin' => '/api/lib/BAApiLogin.php',
    'BAApiException' => '/api/lib/BAApiException.php',
    'BAApiRouter' => '/api/lib/BAApiRouter.php',

    'BAApiRouterLogin' => '/api/lib/routers/BAApiRouterLogin.php',
    'BAApiRouterResidues' => '/api/lib/routers/BAApiRouterResidues.php',


    )
);