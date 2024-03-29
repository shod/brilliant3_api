<?php

namespace App\Models;

use Illuminate\Support\Facades\Redis;
use App\Services\RedisService;

/**
 * Класс для работы с конечными PTP устройствами
 */
class Device
{

  public int $id;
  public string $name;
  public array $location;
  public array $points;
  public array $history;

  //?int $id, ?string $name
  public function __construct()
  {
    $this->id = 0;
    $this->name = '';
    $this->location = ['x' => 0, 'y' => 0];
    $this->points = [];
    $this->history = ['x' => [], 'y' => []];
  }

  public function set($data)
  {
    foreach ($data as $key => $value) {
      $this->{$key} = $value;
    }
  }

  public function find($deviceId)
  {
    $rkey = RedisService::keyEncode(RedisService::KEY_DEVICE, [$deviceId]);
    $res = Redis::get($rkey);
    dd($rkey . '=' . print_r($res, true));
  }
}
