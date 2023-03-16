<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use App\Services\RedisService;
use App\Services\DeviceService;
use App\Models\Device;

class EventRedisCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'event:redis {keyword}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for Events command keyword=[clear]';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $keyword = $this->argument('keyword');

        $this->$keyword();
        return Command::SUCCESS;
    }

    public function clear()
    {
        /**
         * Удалить все события
         */

        $rkey = RedisService::keyEncode(RedisService::KEY_EVENT, ['*']);
        $hosts = Redis::keys($rkey);

        RedisService::deleteKeys($hosts);
        return Command::SUCCESS;
    }

    public function triangle()
    {
        $key = RedisService::keyEncode(RedisService::KEY_DEVICE, ['34851825C972']);
        $res = Redis::get($key);

        //$device = new Device();
        $device = json_decode($res);
        DeviceService::triangulation($device, true);
        $res = Redis::set($key, json_encode($device));
        dd($device);
    }
}
