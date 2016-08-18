<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class BAApiDI {

  private function __construct() {
    global $USER, $APPLICATION;
    if (!is_object($USER)) $USER = new CUser();
    $this->user = $USER;
    $this->app = $APPLICATION;
  }

  private static $instance;
  public function _() {
    if(!isset(self::$instance)) {
      self::$instance = new self();
    }
    return self::$instance;
  }

}