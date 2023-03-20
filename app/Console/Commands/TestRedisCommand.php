<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use App\Services\RedisService;
use App\Models\Device;

class TestRedisCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:redis {keyword}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $keyword = $this->argument('keyword');

        if ($keyword == 'device') {
            $this->showDevices();
        } else {
            $this->showHosts();
            $this->showPoints();
            $this->showDevices();
            $this->showEvents();
            $this->showEventsHistory();
        }
        return Command::SUCCESS;
    }
    private function showDevices()
    {
        //Redis::set('host:', '123');
        $res = Redis::keys('device:*');
        $key = '';
        $this->info('Divices List');
        //$device = new Device();
        foreach ($res as $item) {
            $key = RedisService::keyDecode($item);
            $res = Redis::get($key);
            $this->info($key . '=' . $res);
            $device = new Device();
            $device = json_decode($res);
            //echo ($device->name);
        }
        $this->info('---------------------------');
    }

    private function showEvents()
    {
        $res = Redis::keys('event:*');
        $key = '';
        $this->info('Events');
        foreach ($res as $item) {
            $key = RedisService::keyDecode($item);
            $res = Redis::get($key);
            $this->info($key . '=' . print_r($res, true));
            //var_dump(json_decode($res));
        }
        $this->info('---------------------------');
    }

    private function showEventsHistory()
    {
        $res = Redis::keys('event_history:*');
        $key = '';
        $this->info('EventsHistory');
        foreach ($res as $item) {
            $key = RedisService::keyDecode($item);
            $res = Redis::get($key);
            $this->info($key . '=' . print_r($res, true));
            //var_dump(json_decode($res));
        }
        $this->info('---------------------------');
    }

    private function showHosts()
    {
        $res = Redis::keys(RedisService::KEY_HOST . ':*');
        $key = '';
        $this->info('Hosts');
        foreach ($res as $item) {
            $key = RedisService::keyDecode($item);
            $res = Redis::get($key);
            $this->info($key . '=' . print_r($res, true));
        }
        $this->info('---------------------------');
    }

    private function showPoints()
    {
        $res = Redis::keys(RedisService::KEY_POINT . ':*');
        $key = '';
        $this->info('Points');
        foreach ($res as $item) {
            $key = RedisService::keyDecode($item);
            $res = Redis::get($key);
            $this->info($key . '=' . print_r($res, true));
        }
        $this->info('---------------------------');
    }
}
