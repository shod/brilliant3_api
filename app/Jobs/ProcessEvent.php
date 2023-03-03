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
        echo ('ProcessEvent = ' . $key) . PHP_EOL;
        Redis::set($key, $this->message_origin);
    }
}
