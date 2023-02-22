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
        $this->message = json_decode($message, true);
        $this->message_origin = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        echo 'ProcessEvent' . PHP_EOL;
        $date = Carbon::parse($this->message['time']);

        $key = RedisService::keyEncode(RedisService::KEY_EVENT, [$this->message['device_mac'], $date->timestamp]);
        Redis::set($key, $this->message_origin);
    }
}
