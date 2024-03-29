<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use App\Models\Device;
use Illuminate\Support\Arr;
use App\Services\HelperService;

class DeviceService
{
  public static $rssi_correct = 20;
  public static function synchronize(int $groupids)
  {
    /**
     * The ZabbixApi instance.
     *
     * @var \Becker\Zabbix\ZabbixApi
     */
    $zabbix = app('zabbix');

    /**
     * Удвлить все хосты
     */

    $rkey = RedisService::keyEncode(RedisService::KEY_DEVICE, ['*']);
    $hosts = Redis::keys($rkey);

    RedisService::deleteKeys($hosts);

    /**
     * Получить все устройства
     */
    $rkey = RedisService::keyEncode(RedisService::KEY_DEVICE, ['*']);
    $hosts = Redis::keys($rkey);
    RedisService::deleteKeys($hosts);

    $params = [];
    $params = [
      'groupids' => $groupids, 'selectInventory' => ['macaddress_a'],
      'output' => ['host']
    ];
    $hosts = collect($zabbix->hostGet($params))->map(function ($item) {
      return [
        'id'   => strtoupper($item->hostid),
        'host'   => $item->host,
        'inventory' => $item->inventory,
      ];
    });

    foreach ($hosts as $item) {

      $device = new Device();
      $device->id = $item['id'];
      $device->name = $item['host'];
      $rkey = RedisService::keyEncode(RedisService::KEY_DEVICE, [$item['host']]);
      Redis::set($rkey, json_encode($device));
    }
  }

  /**
   * Расчет позиции устройства
   */
  public static function triangulation(&$device, $debug = false)
  {
    /* 
    * @TODO:сделать через конфиг
    *Получаем дату менше 10 минут от текущей
    */
    date_default_timezone_set('Europe/Minsk');
    $min_period = time() - 1200;
    $rkey = RedisService::keyEncode(RedisService::KEY_EVENT, [$device->name, '*']);

    $events = Redis::keys($rkey);

    $points = [];
    foreach ($events as $item) {

      $key = RedisService::keyDecode($item);
      $event = json_decode(Redis::get($key));
      $point = self::getPoint($event->gw_mac);

      if (!empty($point->x) && strtotime($event->time) > $min_period) {
        $rssi = round(abs($event->rssi) * $point->rssi_ratio);
        $points[] = ['name' => $point->name, 'rssi' => $rssi, 'radius' => round($rssi * 2.2), 'x' => $point->x, 'y' => $point->y, 'time' => strtotime($event->time)]; //, 'x' => $point->x, 'y' => $point->y];
      }
    }

    $sorted = array_values(Arr::sort($points, function ($value) {
      return $value['rssi'];
    }));    

    $points = array_slice($sorted, 0, 3);

    if (count($points) == 3) {
      $location = self::getLocation($points);
      $device->location->x = round($location['x']);
      $device->location->y = round($location['y']);
      $device->points = $points;
      self::historyLocationSave($device);
    }else{
		echo ">>>Count Points only:" . count($points);
		echo "";
		if ($debug) {
		  var_dump($sorted);
		}
	}

    if ($debug) {

      //var_dump($points);
      //var_dump($device);
      /**
       * Фильтрация координат
       */
      //HelperService::FilterDevicePoints($device);
      //var_dump($device);
    }
  }

  /**
   * Сохраняем историю координат
   */
  private static function historyLocationSave(&$device)
  {
    $max_element = 5;
    $location = $device->location;
    $history = $device->history;

    $arr_x = $history->x;
    $arr_y = $history->y;

    if (isset($arr_x[0]) && $arr_x[0] != $location->x) {
      array_unshift($arr_x, $location->x);
    } else {
      $arr_x[] = $location->x;
    }

    if (isset($arr_y[0]) && $arr_y[0] != $location->y) {
      array_unshift($arr_y, $location->y);
    } else {
      $arr_y[] = $location->y;
    }

    $arr_x = array_slice($arr_x, 0, $max_element);
    $arr_y = array_slice($arr_y, 0, $max_element);

    $device->history = ['x' => $arr_x, 'y' => $arr_y];
  }

  /**
   * Получить Данные по устройству
   */
  private static function getPoint($key)
  {
    $rkey = RedisService::keyEncode(RedisService::KEY_POINT, [$key]);
    return json_decode(Redis::get($rkey));
  }

  /**
   * Алгоритм трилетерации
   */
  private static function getLocation($arrPoints)
  {
    $a = 2 * $arrPoints[1]['x'] - 2 * $arrPoints[0]['x'];
    $b = 2 * $arrPoints[1]['y'] - 2 * $arrPoints[0]['y'];
    $c = pow($arrPoints[0]['radius'], 2) - pow($arrPoints[1]['radius'], 2) - pow($arrPoints[0]['x'], 2) + pow($arrPoints[1]['x'], 2) - pow($arrPoints[0]['y'], 2) + pow($arrPoints[1]['y'], 2);
    $d = 2 * $arrPoints[2]['x'] - 2 * $arrPoints[1]['x'];
    $e = 2 * $arrPoints[2]['y'] - 2 * $arrPoints[1]['y'];
    $f = pow($arrPoints[1]['radius'], 2) - pow($arrPoints[2]['radius'], 2) - pow($arrPoints[1]['x'], 2) + pow($arrPoints[2]['x'], 2) - pow($arrPoints[1]['y'], 2) + pow($arrPoints[2]['y'], 2);
    $x = ($c * $e - $f * $b) / ($e * $a - $b * $d);
    $y = ($c * $d - $a * $f) / ($b * $d - $a * $e);
    return ['x' => $x, 'y' => $y];
  }
};
