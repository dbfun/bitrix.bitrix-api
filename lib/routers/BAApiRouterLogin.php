<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class BAApiRouterLogin {

  public function run() {
    $DI = BAApiDI::_();

    $login = $_POST['login'];
    $password = $_POST['password'];


    $arAuthResult = $DI->user->Login($login, $password, "N", "Y"); // N - тогда md5
    $DI->app->arAuthResult = $arAuthResult;

    if($arAuthResult === true) {
      header("HTTP/1.0 200 OK", true);
      die('success');
    }

    $err = isset($arAuthResult['MESSAGE']) ? strip_tags($arAuthResult['MESSAGE']) : 'error';
    header("HTTP/1.0 403 Forbidden", true);
    die($err);
  }

}