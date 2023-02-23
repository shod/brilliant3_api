<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class DeviceService
{
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
        'host'   => strtoupper($item->host),
        'inventory' => $item->inventory,
      ];
    });

    foreach ($hosts as $item) {
      Redis::set('device:' . $item['host'], $item['id']);
    }
  }
}
