<?php

namespace App\Jobs;

use App\Services\RedisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Carbon;

use App\Services\HelperService;
use App\Services\DeviceService;

class ProcessEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $message;
    protected $message_origin;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($message)
    {
        $this->message = HelperService::EventStrToArray($message);
        $this->message_origin = json_encode($this->message);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        echo ('ProcessEvent') . PHP_EOL;
        $date = Carbon::parse($this->message['time']); //->getPreciseTimestamp(3);                
        $key = RedisService::keyEncode(RedisService::KEY_EVENT, [$this->message['device_mac'], $this->message['gw_mac']]);

        echo ('ProcessEvent = ' . $key . ', $deviceId=' . $this->message['device_mac']) . PHP_EOL;
        Redis::set($key, $this->message_origin);
        $event = json_decode(Redis::get($key));

        $key_hist = RedisService::keyEncode(RedisService::KEY_EVENT_HISTORY, [$this->message['device_mac'], $this->message['gw_mac']]);
        $this->rssiHistorySave($key_hist, $this->message['rssi']);

        HelperService::FilterHost($event);
        Redis::set($key, json_encode($event));

        $this->triangle($this->message['device_mac']);
    }

    /** 
     * Сохранение истории rssi 
     * */
    private function rssiHistorySave($key, $rssi)
    {
        $max_element = 5;
        $event_history = json_decode(Redis::get($key));
        if ($event_history === null) {
            Redis::set($key, json_encode([$rssi]));
        } else {
            array_unshift($event_history, $rssi);
            $rssi_history = array_slice($event_history, 0, $max_element);
            Redis::set($key, json_encode($rssi_history));
        }
    }

    /**
     * Расчет координат
     */
    private function triangle($deviceId)
    {
        $key = RedisService::keyEncode(RedisService::KEY_DEVICE, [$deviceId]);
        $res = Redis::get($key);

        $device = json_decode($res);
        DeviceService::triangulation($device);
        HelperService::FilterDevicePoints($device);

        $res = Redis::set($key, json_encode($device));
    }
}
