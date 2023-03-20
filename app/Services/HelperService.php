<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

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
   * Фильтрация данных для устройства по координатам
   * */
  public static function FilterDevicePoints(&$device)
  {

    $filterFunctions = ['fp_jump'];
    foreach ($filterFunctions as $function) {
      self::$function($device);
    }
  }

  /**
   * Фильтрация данных для шлюза по уровню сигнала
   * */
  public static function FilterHost(&$event)
  {
    $filterFunctions = ['fh_rssi_avg'];

    foreach ($filterFunctions as $function) {
      self::$function($event);
    }
  }

  /**
   * Усреднение значения сигнала
   */
  private static function fh_rssi_avg(&$event)
  {
    $key = RedisService::keyEncode(RedisService::KEY_EVENT_HISTORY, [$event->device_mac, $event->gw_mac]);
    $event_history = json_decode(Redis::get($key));
    $average = round(array_sum($event_history) / count($event_history));
    $event->rssi = $average;
  }

  /** 
   * Отсекание прыжков по координатам выше 10%
   */
  private static function fp_jump(&$device)
  {
    $perc_delta = 10;
    $location = $device->location;

    $history_x = $device->history['x'];
    $history_y = $device->history['y'];

    if (!empty($history_x) && count($history_x) > 2) {
      $hist = $history_x[1];

      $curr_percent = 100 - ($hist * 100 / $location->x);
      if ($curr_percent >= $perc_delta) {
        $device->location->x = $history_x[2];
      }
    }

    if (!empty($history_y) && count($history_y) > 2) {
      $hist = $history_y[1];

      $curr_percent = 100 - ($hist * 100 / $location->x);
      if ($curr_percent >= $perc_delta) {

        $device->location->y = $history_y[2];
      }
    }
    //var_dump($device->location);
  }
}
