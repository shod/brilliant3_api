<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\Redis;

class ProcessEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $message;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($message)
    {
        $this->message = json_decode($message, true);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        echo '2';
        //dd($this->message);
        Redis::set('event:' . $this->message['device_id'], $this->message);
        //Redis::set('navi:' . $this->message['device_id'], $this->message);
    }
}
