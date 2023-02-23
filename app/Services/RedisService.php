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

  public static function keyDecode($row_key): string
  {
    $prefix = Config::get('database.redis.options.prefix');
    return substr($row_key, strlen($prefix));
  }

  public static function keyEncode($event, $arr_key): string
  {
    return $event . ':' . implode(':', $arr_key);
  }

  public static function deleteKeys(array $keys)
  {
    foreach ($keys as $id => $key) {
      Redis::del(RedisService::keyDecode($key));
    }
  }
}
