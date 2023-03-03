<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\Facades\MQTT;
use App\Jobs\ProcessEvent;

class TestMqttCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:mqtt';

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
        /*
        {
        "type": "100",
        "device_mac": "3485182548ca",
        "gw_mac": "8c4b14164d1c",
        "rssi": "-69",
        "time": "2022-12-16 11:36:35.000"
        }
        */

        /** @var \PhpMqtt\Client\Contracts\MqttClient $mqtt */
        $mqtt = MQTT::connection();
        $mqtt->subscribe('Event/Navi/RSSI', function (string $topic, string $message) {
            //echo sprintf('Received QoS level 1 message on topic [%s]: %s', $topic, $message);    
            $this->info('Event');
            dispatch(new ProcessEvent($message));
        }, 1);
        $mqtt->loop(true);
        return Command::SUCCESS;
    }
};
