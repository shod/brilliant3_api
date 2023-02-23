<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use App\Models\Device;

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

      $device = new Device();
      $device->id = $item['id'];
      $device->name = $item['host'];
      $rkey = RedisService::keyEncode(RedisService::KEY_DEVICE, [$item['host']]);
      Redis::set($rkey, json_encode($device));
    }
  }
}