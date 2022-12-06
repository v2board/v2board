<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Encryption\Encrypter;
use App\Models\User;
use App\Utils\Helper;
use Illuminate\Support\Facades\DB;

class V2boardInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2board:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'v2board Installation';

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
        try {
            $this->info("__     ______  ____                      _  ");
            $this->info("\ \   / /___ \| __ )  ___   __ _ _ __ __| | ");
            $this->info(" \ \ / /  __) |  _ \ / _ \ / _` | '__/ _` | ");
            $this->info("  \ V /  / __/| |_) | (_) | (_| | | | (_| | ");
            $this->info("   \_/  |_____|____/ \___/ \__,_|_|  \__,_| ");

            $this->info("+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+");
            $this->info("Translated by Mr.Nobody https://tt.vg/v2board");

            if (\File::exists(base_path() . '/.env')) {
                abort(500, 'V2board is already installed, if you want to reinstall it, please delete the .env file in the directory');
            }

            if (!copy(base_path() . '/.env.example', base_path() . '/.env')) {
                abort(500, 'Failed to copy environment file, please check the directory permission');
            }
            $this->saveToEnv([
                'APP_KEY' => 'base64:' . base64_encode(Encrypter::generateKey('AES-256-CBC')),
                'DB_HOST' => $this->ask('Please enter the database address (default:localhost)', 'localhost'),
                'DB_DATABASE' => $this->ask('Please enter the database name'),
                'DB_USERNAME' => $this->ask('Please enter the database user name'),
                'DB_PASSWORD' => $this->ask('Please enter the database password')
            ]);
            \Artisan::call('config:clear');
            \Artisan::call('config:cache');
            try {
                DB::connection()->getPdo();
            } catch (\Exception $e) {
                abort(500, 'Database connection failure');
            }
            $file = \File::get(base_path() . '/database/install.sql');
            if (!$file) {
                abort(500, 'Database file does not exist');
            }
            $sql = str_replace("\n", "", $file);
            $sql = preg_split("/;/", $sql);
            if (!is_array($sql)) {
                abort(500, 'The database file format is wrong');
            }
            $this->info('Please wait while the database is imported...');
            foreach ($sql as $item) {
                try {
                    DB::select(DB::raw($item));
                } catch (\Exception $e) {
                }
            }
            $this->info('Database import completed');
            $email = '';
            while (!$email) {
                $email = $this->ask('Please enter administrator email?');
            }
            $password = '';
            while (!$password) {
                $password = $this->ask('Please enter the administrator password?');
            }
            if (!$this->registerAdmin($email, $password)) {
                abort(500, 'Administrator account registration failed, please try again');
            }

            $this->info('Finished...Everything is ready');
            $this->info('Visit http(s)://your-site/admin to access the administration panel');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    private function registerAdmin($email, $password)
    {
        $user = new User();
        $user->email = $email;
        if (strlen($password) < 8) {
            abort(500, 'Minimum administrator password length is 8 characters');
        }
        $user->password = password_hash($password, PASSWORD_DEFAULT);
        $user->uuid = Helper::guid(true);
        $user->token = Helper::guid();
        $user->is_admin = 1;
        return $user->save();
    }

    private function saveToEnv($data = [])
    {
        function set_env_var($key, $value)
        {
            if (! is_bool(strpos($value, ' '))) {
                $value = '"' . $value . '"';
            }
            $key = strtoupper($key);

            $envPath = app()->environmentFilePath();
            $contents = file_get_contents($envPath);

            preg_match("/^{$key}=[^\r\n]*/m", $contents, $matches);

            $oldValue = count($matches) ? $matches[0] : '';

            if ($oldValue) {
                $contents = str_replace("{$oldValue}", "{$key}={$value}", $contents);
            } else {
                $contents = $contents . "\n{$key}={$value}\n";
            }

            $file = fopen($envPath, 'w');
            fwrite($file, $contents);
            return fclose($file);
        }
        foreach($data as $key => $value) {
            set_env_var($key, $value);
        }
        return true;
    }
}
