# Веб-сервисы для работы с сайтом на Bitrix

Веб-сервисы работают по протоколу `HTTP(S)`, для доступа необходима авторизация. Примеры на PHP - в приложении.

## 1. Авторизация

Авторизация осуществляется по логину и паролю, как обычный пользователь.

Желательно создать для работы с API отдельного пользователя.

Для авторизации необходимо по URL https://site.ru/api/login/ в POST запросе передать логин и пароль, в ответ сервер вернет кукисы и результат авторизации.

```
URL: https://site.ru/api/login/
METHOD: POST
POST['login'] - логин
POST['password'] - пароль
```

Пример авторизации средствами curl:

```
curl -c cookies.txt https://site.ru/api/login/ --data "login=your@mail.ru&password=your-password"
```

| Результат            | Ответ                                                    | Заголовок              |
|----------------------|----------------------------------------------------------|------------------------|
| Успешная авторизация | success                                                  | HTTP/1.0 200 OK        |
| Ошибка авторизации   | Incorrect login or password (или другое описание ошибки) | HTTP/1.0 403 Forbidden |

При множественной неудачной авторизации вход блокируется, необходимо проверить логин и пароль путем ручной авторизации, используя логин и пароль от API, при необходимости ввести капчу.

## 2. API остатков по складам

После авторизации следует запросить URL: https://site.ru/api/residues/json/, используя сохраненные куки

В ответе будут остатки в формате JSON (описание ниже), заголовок ответа `HTTP/1.0 200 OK`. Других форматов (пока) не предусмотрено

```
URL: https://site.ru/api/residues/json/
METHOD: GET
```

Пример получения остатков:

```
curl -b cookies.txt https://site.ru/api/residues/json/
```

## 3. Неправильный вызов

При неудачных запросах возвращается заголовок `HTTP/1.0 403 Forbidden` и сообщение об ошибке.

| Текст ошибки                        | Описание                      |
|-------------------------------------|-------------------------------|
| No such router: BAApiRouterResidues | Вызов несуществующего роутера |
| Authorization required              | Обращение без авторизации     |

# Описание формата остатков по складам

Формат выгрузки - JSON. Пример для двух позиций на трех складах:

```
{
  "stores": {
    "aeef2063-0113-b0d7-8255-00155d032904": {
      "name": "Москва склад",
      "abbr": "М"
    },
    "47585e53-6085-11d9-11e0-00001a1a02c3": {
      "name": "Нижний Новгород склад",
      "abbr": "Н"
    },
    "1d437496-c1e7-11e2-af7a-003048d2334c": {
      "name": "Рязань склад",
      "abbr": "Р"
    }
  },
  "shopItems": [
    {
      "sku": "MSX800-120",
      "residues": {
        "aeef2063-0113-b0d7-8255-00155d032904": "10",
        "47585e53-6085-11d9-11e0-00001a1a02c3": "0",
        "1d437496-c1e7-11e2-af7a-003048d2334c": "10"
      }
    },
    {
      "sku": "MSX800-121",
      "residues": {
        "aeef2063-0113-b0d7-8255-00155d032904": "5",
        "47585e53-6085-11d9-11e0-00001a1a02c3": "10",
        "1d437496-c1e7-11e2-af7a-003048d2334c": "10"
      }
    }
  ]
}
```

Поля:

* `stores` - коллекция складов, объект, где ключ - `GUID` склада, с его описанием (название `name` и аббревиатура `abbr`)
* `shopItems` - коллекция товаров, массив, где указан артикул (`sku`) и остатки (`residues`), где каждому складу соответствует остаток

# Приложение 1. Пример получения остатков на PHP

```
<?php

// Пример на PHP для получения остатков товаров с сайта site.ru

class ApiExample {

  private $apiLogin = 'your@mail.ru', $apiPassword = 'your-password';
  public function __construct() {
    set_time_limit(180); // время выполнения до 180 сек
    $this->initCurl();
  }

  public function run() {
    try {
      $this->curlResp('https://site.ru/api/login/', array('login' => $this->apiLogin, 'password' => $this->apiPassword));
      $this->curlResp('https://site.ru/api/residues/json/');
      $fh = fopen('residues.json', 'w');
      fwrite($fh, $this->response);
      fclose($fh);
      echo 'Done: residues.json';
    } catch (Exception $e) {
      echo $e->GetMessage();
    }
  }

  private $curl, $response, $httpCode;
  private function initCurl() {
    $this->curl = curl_init();
    curl_setopt($this->curl, CURLOPT_HEADER,           false);
    curl_setopt($this->curl, CURLOPT_RETURNTRANSFER,   true);
    curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT,   30);
    curl_setopt($this->curl, CURLOPT_COOKIEFILE,       "");
    curl_setopt($this->curl, CURLOPT_COOKIEJAR,        "");

    // если появляется ошибка HTTPS, то расскоментировать 1 или 2 вариант

    // 1 вариант - указать цепочку сертификатов вручную
    // файл сертификата, как скачать цепочку описано здесь http://unitstep.net/blog/2009/05/05/using-curl-in-php-to-access-https-ssltls-protected-sites/

    /*
    $certFile = 'site.ru.crt';
    if(!file_exists($certFile)) throw new Exception("No cert file ".$certFile, 1);
    curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($this->curl, CURLOPT_CAINFO, $certFile);
    */

    // 2 вариант - отменить проверку сертификата полностью

    /*
    curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER,   false); // SSL skip
    */


  }

  private function curlResp($uri, $postData = false) {
    curl_setopt($this->curl, CURLOPT_URL, $uri);
    if($postData !== false) {
      curl_setopt($this->curl, CURLOPT_POST, true);
      curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postData);
    } else {
      curl_setopt($this->curl, CURLOPT_POST, false);
    }
    $this->response = curl_exec($this->curl);
    $this->httpCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

    $errNo = curl_errno($this->curl);
    if($errNo != 0) throw new Exception("Curl error: ".$errNo.", see codes here: http://php.net/manual/ru/function.curl-errno.php", $errNo);

    if($this->httpCode !== 200) throw new Exception($this->response, $this->httpCode);
    if(empty($this->response)) throw new Exception("Empty response", $this->httpCode);
  }

}

$ApiExample = new ApiExample();
$ApiExample->run();
```

