<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

// Роутер

class BAApiRouter {

  private $router;
  public function __construct() {
    $this->path = $_GET['path'];
    $this->chunks = explode('/', $this->path);
    $this->chunks = array_filter($this->chunks);
  }

  public function run() {
    try {
      $this->getRouter();
      $this->router->run();
      die();
    } catch (Exception $e) {
      header("HTTP/1.0 403 Forbidden", true);
      echo $e->getMessage();
      die();
    }
  }

  private function getRouter() {
    $this->routerName = 'BAApiRouter'.ucfirst($this->chunks[0]);
    if($this->routerName == 'BAApiRouter') throw new Exception("API help: https://iek.ru/partner/personal/help/", 1);
    if(!class_exists($this->routerName)) throw new Exception("No such router: ".$this->routerName, 1);
    $this->router = new $this->routerName;
    if(!method_exists($this->router, 'run')) throw new Exception("No run function in router: ".$this->routerName, 1);
  }

}