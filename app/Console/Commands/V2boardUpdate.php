<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class V2boardUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2board:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'v2board Updating';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        \Artisan::call('config:cache');
        DB::connection()->getPdo();
        $file = \File::get(base_path() . '/database/update.sql');
        if (!$file) {
            abort(500, 'The database file does not exist');
        }
        $sql = str_replace("\n", "", $file);
        $sql = preg_split("/;/", $sql);
        if (!is_array($sql)) {
            abort(500, 'The database file format is incorrect');
        }
        $this->info('Please wait while the database is imported...');
        foreach ($sql as $item) {
            if (!$item) continue;
            try {
                DB::select(DB::raw($item));
            } catch (\Exception $e) {
            }
        }
        \Artisan::call('horizon:terminate');
        $this->info('The update is complete, the queue service has been restarted, and you do not need to do anything.');
    }
}
