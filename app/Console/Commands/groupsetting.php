<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\TestController;

class groupsetting extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:groupsetting';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '2.7.4 → 2.8 groupIDsetting';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $controller = new TestController();
        $controller->groupsetting();
        return Command::SUCCESS;
    }
}
