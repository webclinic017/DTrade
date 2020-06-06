<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DispatchJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:dispatch {job}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatches the given job if it exists';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $class = '\\App\\Jobs\\'.$this->argument('job');
        if (!class_exists($class)) {
            $this->error('Job does not exist.');

            return  1;
        }

        return dispatch(new $class());
    }
}
