<?php

namespace App\Services;

use App\Models\Device;

class HelperService
{

  /**
   * "type=100;device_mac=34851825c972;gw_mac=24d7ebbb6754;rssi=-58;time=2023-03-02 23:14:08.000;"
   * 
   */
  public static function EventStrToArray(string $string)
  {
    $parts = explode(';', $string); // разбиваем строку по разделителю ";"    
    $data = []; // создаем пустой массив для хранения данных

    foreach ($parts as $part) {
      $pair = explode('=', $part); // разбиваем каждую часть по разделителю "="            
      $key = $pair[0]; // первая часть - ключ
      if (!empty($key)) {
        $value = $pair[1]; // вторая часть - значение
        $data[$key] = $value; // добавляем ключ-значение в массив данных
      }
    }
    return $data;
  }

  /**
   * Расчет координат методом триангуляции 
   * */  
  public static function RecalcLocaton(string $deviceId){
    
  }
}
