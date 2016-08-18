<?php

// UTF8!

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class BAApiRouterResidues {

  public function __construct() {
    CModule::IncludeModule("iblock");
    CModule::IncludeModule("catalog");
    CModule::IncludeModule("sale");
  }

  public function run() {
    try {
      $this->accessGranted();
      $this->response();
    } catch (Exception $e) {
      header("HTTP/1.0 403 Forbidden", true);
      echo $e->getMessage();
      die();
    }

  }

  const PARTNER_GROUP_ID = 31;
  protected function accessGranted() {
    $user = BAApiDI::_()->user;
    if(!$user->IsAuthorized()) throw new Exception("Authorization required", 1);
    $groups = $user->GetUserGroupArray();
    $isOk = in_array(self::PARTNER_GROUP_ID, $groups);
    if(!$isOk) throw new Exception("Access denied", 1);
  }

  private function response() {
    $this->getItems();
    $this->responseJSON();
  }

  // COPY PASTE FROM component
  // TODO

  private $shopItems, $SKLADI;
  private function getItems() {
    $this->shopItems = array();
    $arResult["PARTNER"] = IEKDiscount::getPartnerByUserId(BAApiDI::_()->user->GetID());
    $arResult["SKLADI"] = IEKDiscount::getSkladiByPartner($arResult["PARTNER"]);

    $arMinElemFilter = $arElemFilter = array("ACTIVE" => "Y", "SECTION_GLOBAL_ACTIVE" => "Y", "IBLOCK_ACTIVE" => "Y", "IBLOCK_ID" => DEALER_CATALOG_IBID);
    $dbElems = CIBlockElement::GetList(array('SORT' => 'ASC', 'NAME' => 'ASC'), $arElemFilter, false, false,
      array('ID', 'IBLOCK_ID', 'NAME', 'XML_ID', 'CATALOG_GROUP_2', 'CATALOG_GROUP_3'));

    while ($obElem = $dbElems->GetNextElement()) {
      list($shopItem, $shopItemProps) = IEKDiscount::getShopItemBasicInfo($obElem);

      // остаток
      $shopItem['OSTATOK_OT'] = $shopItem['OSTATOK_DO'] = 0;
      $shopItem['OSTATOK_SKLADI'] = array();
      foreach ($shopItemProps['OT_I_DO']['DESCRIPTION'] as $k1 => $v1) {
        if (isset($arResult['SKLADI'][$shopItemProps['OT_I_DO']['VALUE'][$k1]])) {
          $arOtIDo = explode('@', $v1);
          $shopItem['OSTATOK_OT'] += intval($arOtIDo[0]);
          $shopItem['OSTATOK_DO'] += intval($arOtIDo[1]);
          $shopItem['OSTATOK_SKLADI'][$shopItemProps['OT_I_DO']['VALUE'][$k1]] = array(
              'OT'  => intval($arOtIDo[0]),
              'DO'  => intval($arOtIDo[1]),
          );
        }
      }

      $shopItem['RESIDUES'] = array();
      foreach ($shopItemProps['RESIDUES']['DESCRIPTION'] as $k1 => $v1) {
        if (isset($arResult['SKLADI'][$shopItemProps['RESIDUES']['VALUE'][$k1]])) {
          $shopItem['RESIDUES'][$shopItemProps['RESIDUES']['VALUE'][$k1]] = $v1;
        }
      }

      $this->SKLADI = $arResult["SKLADI"];
      $this->shopItems[] = $shopItem;
    }
    if(count($this->shopItems) == 0) throw new Exception("No shop items!", 1);
  }

  private function responseJSON() {
    $resp = array('stores' => array(), 'shopItems' => array());

    foreach ($this->SKLADI as $k2 => $v2) {
      $resp['stores'][$k2] = array(
        'name' => iconv('cp1251', 'utf-8//IGNORE', $v2['NAME']),
        'abbr' => iconv('cp1251', 'utf-8//IGNORE', $v2['ABBR'])
      );
    }

    foreach($this->shopItems as $arItem) {
      $it = array();
      $it['sku'] = $arItem["CML2_ARTICLE"];

      foreach ($this->SKLADI as $k2 => $v2) {
        $count = iconv('cp1251', 'utf-8//IGNORE', IEKDiscount::formatResidue($arItem["RESIDUES"][$k2], $arItem["EXPECTED"][$k2], true));
        $it['residues'][$k2] = $count;
      }


      $resp['shopItems'][] = $it;
    }
    die(json_encode($resp));
  }

}