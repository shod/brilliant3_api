<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Redis;
use Illuminate\Console\Command;

class SystemInfoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:info';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Database info';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        return Command::SUCCESS;
    }
}
