<?php

namespace App\Console\Commands;

use App\Jobs\SendEmailJob;
use Illuminate\Console\Command;

class DataBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '数据备份';

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
        $backupEmail=config('database.connections.db_backup_email');
        if(isset($backupEmail)||empty($backupEmail)){
            $this->dataBackupDay($backupEmail);
        }
    }

    private function dataBackupDay($backupEmail):void
    {
        $backupPath=config('database.connections.db_backup_path');
        if(!isset($backupPath)||empty($backupPath)){
            echo "备份路径不能为空";
            exit;
        }
        if(file_exists($backupPath)){
            system("rm -rf ".$backupPath, $ret);
        }
        mkdir($backupPath);
        $mysqldump=system('which mysqldump',$ret);
        if ( $ret == '0' ) {
            echo "\n开始进行备份\n";
            system($mysqldump.' --user='.config('database.connections.mysql.username').' --password='.config('database.connections.mysql.password').' --host='.config('database.connections.mysql.host').' -P '.config('database.connections.mysql.port').' '.config('database.connections.mysql.database').' v2_coupon v2_invite_code v2_notice v2_order v2_plan v2_server v2_server_group v2_ticket v2_ticket_message v2_tutorial v2_user >'.$backupPath.'/v2board_back.sql', $ret);

            system($mysqldump.' --opt --user='.config('database.connections.mysql.username').' --password='.config('database.connections.mysql.password').' --host='.config('database.connections.mysql.host').' -P '.config('database.connections.mysql.port').' '.config('database.connections.mysql.database').' -d failed_jobs v2_mail_log v2_server_log >> '.$backupPath.'/v2board_back.sql', $ret);
        }

        system("cp ".base_path('.env')." ".$backupPath."/env.bak", $ret);
        system("cp ".base_path('config/v2board.php')." ".$backupPath."/v2board.php.bak", $ret);
        $backupPassword=config('database.connections.db_backup_password');
        if(isset($backupPassword)||empty($backupPassword)){
            system("zip -r ".$backupPath."/v2board_backup.zip ".$backupPath." -x ".$backupPath."/v2board_backup.zip -P ".$backupPassword, $ret);
        } else {
            system("zip -r ".$backupPath."/v2board_backup.zip ".$backupPath." -x ".$backupPath."/v2board_backup.zip", $ret);
        }

        SendEmailJob::dispatch([
            'email' => $backupEmail,
            'subject' => config('v2board.app_name', 'V2board') . '-备份成功',
            'attachment' => $backupPath.'/v2board_backup.zip',
            'template_name' => 'dataBackcup',
            'template_value' => [
                'name' => config('v2board.app_name', 'V2Board'),
                'url' => config('v2board.app_url')
            ]
        ]);
    }
}
