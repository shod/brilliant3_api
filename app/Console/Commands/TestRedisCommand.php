<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use App\Services\RedisService;

class TestRedisCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:redis';

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
        $this->showHosts();
        $this->showDevices();
        $this->showEvents();
        return Command::SUCCESS;
    }
    private function showDevices()
    {
        //Redis::set('host:', '123');
        $res = Redis::keys('device:*');
        $key = '';
        $this->info('Divices List');
        foreach ($res as $item) {
            $key = RedisService::keyDecode($item);
            $res = Redis::get($key);
            $this->info($key . '=' . $res);
            var_dump(json_decode($res));
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

    private function showHosts()
    {
        $res = Redis::keys(RedisService::KEY_HOST . ':*');
        $key = '';
        $this->info('Hosts');
        foreach ($res as $item) {
            $key = RedisService::keyDecode($item);
            $res = Redis::get($key);
            //$this->info($key . '=' . print_r($res, true));            
            var_dump(json_decode($res));
        }
        $this->info('---------------------------');
    }
}
