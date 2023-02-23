<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use App\Services\RedisService;
use App\Services\HostService;
use App\Services\DeviceService;

class TestZabbixCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:zabbix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * The ZabbixApi instance.
     *
     * @var \Becker\Zabbix\ZabbixApi
     */
    protected $zabbix;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->zabbix = app('zabbix');

        /**
         * Получение групп маршрутизаторов и устройств
         */
        $params = [];
        $params = ['filter' => ['name' => ['GW', 'PTP']]];
        $hostgroups = collect($this->zabbix->hostgroupGet($params))->map(function ($item) {
            return [
                'id'   => strtoupper($item->groupid),
                'name' => strtoupper($item->name)
            ];
        });

        $hostgroups = $hostgroups->keyBy(function ($item, $key) {
            return strtoupper($item['name']);
        });


        HostService::synchronize($hostgroups['GW']['id']);

        DeviceService::synchronize($hostgroups['PTP']['id']);

        return Command::SUCCESS;
    }
}
