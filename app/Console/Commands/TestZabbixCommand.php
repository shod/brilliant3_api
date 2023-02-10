<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

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


        /**
         * Получить все шлюзы
         */
        $params = [];
        $params = [
            'groupids' => $hostgroups['GW']['id'], 'selectInventory' => ['macaddress_a'],
            'output' => ['host']
        ];
        $hosts = collect($this->zabbix->hostGet($params))->map(function ($item) {
            return [
                'id'   => strtoupper($item->hostid),
                'host'   => strtoupper($item->host),
                'inventory' => $item->inventory,
            ];
        });

        foreach ($hosts as $item) {
            Redis::set('host:' . $item['inventory']->macaddress_a, $item['id']);
        }

        /**
         * Получить все устройства
         */
        $params = [];
        $params = [
            'groupids' => $hostgroups['PTP']['id'], 'selectInventory' => ['macaddress_a'],
            'output' => ['host']
        ];
        $hosts = collect($this->zabbix->hostGet($params))->map(function ($item) {
            return [
                'id'   => strtoupper($item->hostid),
                'host'   => strtoupper($item->host),
                'inventory' => $item->inventory,
            ];
        });

        foreach ($hosts as $item) {
            Redis::set('device:' . $item['host'], $item['id']);
        }

        return Command::SUCCESS;
    }
}