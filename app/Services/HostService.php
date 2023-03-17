<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class HostService
{

  public static function synchronize(int $groupids)
  {
    echo 'Hosts' . PHP_EOL;
    /**
     * The ZabbixApi instance.
     *
     * @var \Becker\Zabbix\ZabbixApi
     */
    $zabbix = app('zabbix');

    $host_info = [];
    $host_point = [];

    /**
     * Удалить все хосты
     */

    $rkey = RedisService::keyEncode(RedisService::KEY_HOST, ['*']);
    $hosts = Redis::keys($rkey);

    RedisService::deleteKeys($hosts);

    /**
     * Удалить все points
     */

    $rkey = RedisService::keyEncode(RedisService::KEY_POINT, ['*']);
    $hosts = Redis::keys($rkey);

    RedisService::deleteKeys($hosts);

    /**
     * Получить все шлюзы
     */
    $params = [];
    $params = [
      'groupids' => $groupids, 'selectInventory' => ['macaddress_a', 'tag'],
      'output' => ['name', 'host']
    ];
    $hosts = collect($zabbix->hostGet($params))->map(function ($item) {
      return [
        'id'   => strtoupper($item->hostid),
        'name'   => strtoupper($item->name),
        'host'   => strtoupper($item->host),
        'inventory' => $item->inventory,
      ];
    });

    foreach ($hosts as $item) {
      echo $item['name'] . PHP_EOL;
      $host_info[$item['name']] = [
        'name' => $item['name'],
        'id' => $item['id'],
        'macaddress_a' => $item['inventory']->macaddress_a,
        'tag' => ($item['inventory']->tag == '') ? 1 : $item['inventory']->tag,
      ];

      //$host_point[$item['inventory']->macaddress_a] = ['x' => 0, 'y' => 0];

      // Save the host info
      $key = RedisService::keyEncode(RedisService::KEY_HOST, [$item['name']]);
      Redis::set($key, json_encode($host_info[$item['name']]));
    }

    /**
     * Получить координаты шлюзов
     */
    $params = [
      'sysmapids' => 2,
      "selectSelements" => "extend",
    ];
    $hosts = collect($zabbix->mapGet($params))->map(function ($item) {
      return [
        'width'   => strtoupper($item->width),
        'height'   => strtoupper($item->height),
        'selements' => $item->selements,
      ];
    });
    Redis::set('map', ['width' => $hosts[0]['width'], 'height' => $hosts[0]['height']]);

    foreach ($hosts[0]['selements'] as $item) {

      if (array_key_exists($item->label, $host_info)) {
        /** 
         * Записать координат        
         */
        $key_mac = $host_info[$item->label]['macaddress_a'];
        $host_point = ['name' => $host_info[$item->label]['name'], 'x' => $item->x, 'y' => $item->y, 'rssi_ratio' => $host_info[$item->label]['tag']];
        $key = RedisService::keyEncode(RedisService::KEY_POINT, [$key_mac]);
        Redis::set($key, json_encode($host_point));
      }
    }
  }
}
