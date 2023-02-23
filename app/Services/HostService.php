<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class HostService
{

  public static function synchronize(int $groupids)
  {
    /**
     * The ZabbixApi instance.
     *
     * @var \Becker\Zabbix\ZabbixApi
     */
    $zabbix = app('zabbix');

    $host_info = [];

    /**
     * Удвлить все хосты
     */

    $rkey = RedisService::keyEncode(RedisService::KEY_HOST, ['*']);
    $hosts = Redis::keys($rkey);

    RedisService::deleteKeys($hosts);

    /**
     * Получить все шлюзы
     */
    $params = [];
    $params = [
      'groupids' => $groupids, 'selectInventory' => ['macaddress_a'],
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
      $host_info[$item['name']] = [
        'name' => $item['name'],
        'id' => $item['id'],
        'macaddress_a' => $item['inventory']->macaddress_a
      ];
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
        $host_data = array_merge(
          $host_info[$item->label],
          [
            'x' => $item->x,
            'y' => $item->y,
          ]
        );

        $key = RedisService::keyEncode(RedisService::KEY_HOST, [$item->label]);

        Redis::set($key, json_encode($host_data));
      }
    }
  }
}
