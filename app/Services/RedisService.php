<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;

class RedisService
{
  const KEY_MAP = 'map';
  const KEY_HOST = 'host';
  const KEY_DEVICE = 'device';
  const KEY_EVENT = 'event';
  const KEY_POINT = 'point';

  public static function keyDecode($row_key): string
  {
    $prefix = Config::get('database.redis.options.prefix');
    return substr($row_key, strlen($prefix));
  }

  public static function keyEncode($event, $arr_key): string
  {
    $arr_key = array_map(function ($item) {
      return strtoupper($item);
    }, $arr_key);

    return $event . ':' . implode(':', $arr_key);
  }

  public static function deleteKeys(array $keys)
  {
    foreach ($keys as $id => $key) {
      Redis::del(RedisService::keyDecode($key));
    }
  }
}
