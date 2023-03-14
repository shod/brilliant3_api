<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use App\Models\Device;
use Illuminate\Support\Arr;

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
    $min_period = time() - 600;
    $rkey = RedisService::keyEncode(RedisService::KEY_EVENT, [$device->name, '*']);

    $events = Redis::keys($rkey);

    $points = [];
    foreach ($events as $item) {

      $key = RedisService::keyDecode($item);
      $event = json_decode(Redis::get($key));
      $point = self::getPoint($event->gw_mac);

      if (!empty($point->x) && strtotime($event->time) > $min_period) {
        //$rssi = abs($event->rssi);
        $rssi = abs($event->rssi) * 0.35;
        $points[] = ['name' => $point->name, 'rssi' => $rssi, 'x' => $point->x, 'y' => $point->y, 'time' => strtotime($event->time)]; //, 'x' => $point->x, 'y' => $point->y];
      }
    }

    $sorted = array_values(Arr::sort($points, function ($value) {
      return $value['rssi'];
    }));

    $points = array_slice($sorted, -3);
    //$points = array_slice($sorted, 3);

    if (count($points) == 3) {
      $location = self::getLocation($points);
      $device->location->x = round($location['x'], 4);
      $device->location->y = round($location['y'], 4);
    }

    if ($debug) {
      var_dump($points);
      dd($device);
    }
  }

  /**
   * Получить Данные по устройству
   */
  private static function getPoint($key)
  {
    $rkey = RedisService::keyEncode(RedisService::KEY_POINT, [$key]);
    return json_decode(Redis::get($rkey));
  }

  private static function getLocation2(array $arrPoints): array
  {
    $location = ['x' => 0, 'y' => 0];
    $x_cos = 0;
    $y_cos = 0;

    $axis = [30, 45, 60];

    $i = 0;
    foreach ($arrPoints as $item) {
      var_dump($item);
      $x_cos = $x_cos + ($item['rssi'] * cos($axis[$i]));
      $y_cos = $y_cos + ($item['rssi'] * cos($axis[$i]));
      echo ($item['rssi'] * cos($axis[$i])) . PHP_EOL;
      $i++;
    }

    dd($x_cos);
    return $location;
  }

  /**
   * Алгоритм трилетерации
   */
  private static function getLocation($arrPoints)
  {
    $a = 2 * $arrPoints[1]['x'] - 2 * $arrPoints[0]['x'];
    $b = 2 * $arrPoints[1]['y'] - 2 * $arrPoints[0]['y'];
    $c = pow($arrPoints[0]['rssi'], 2) - pow($arrPoints[1]['rssi'], 2) - pow($arrPoints[0]['x'], 2) + pow($arrPoints[1]['x'], 2) - pow($arrPoints[0]['y'], 2) + pow($arrPoints[1]['y'], 2);
    $d = 2 * $arrPoints[2]['x'] - 2 * $arrPoints[1]['x'];
    $e = 2 * $arrPoints[2]['y'] - 2 * $arrPoints[1]['y'];
    $f = pow($arrPoints[1]['rssi'], 2) - pow($arrPoints[2]['rssi'], 2) - pow($arrPoints[1]['x'], 2) + pow($arrPoints[2]['x'], 2) - pow($arrPoints[1]['y'], 2) + pow($arrPoints[2]['y'], 2);
    $x = ($c * $e - $f * $b) / ($e * $a - $b * $d);
    $y = ($c * $d - $a * $f) / ($b * $d - $a * $e);
    return ['x' => $x, 'y' => $y];
  }
}
