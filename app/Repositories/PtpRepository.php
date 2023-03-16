<?php

namespace App\Repositories;

use App\Interfaces\PtpRepositoryInterface;
use Illuminate\Support\Facades\Redis;
use App\Services\RedisService;
use App\Models\Device;

class PtpRepository implements PtpRepositoryInterface
{
  public function getSessions($programId)
  {
    abort(404, "Method not implemented");
  }
  public function getPtpById($ptpId): array
  {
    $key = RedisService::keyEncode(RedisService::KEY_DEVICE, [$ptpId]);
    $res = Redis::get($key);
    $device = json_decode($res);

    return [['id' => $ptpId, 'x' => round($device->location->x), 'y' => round($device->location->y), 'points' => $device->points]];
  }
}
