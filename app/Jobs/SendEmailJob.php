<?php

namespace App\Jobs;

use App\Models\MailLog;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Postal\Client;
use Postal\Send\Message;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 10;
    protected $params;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($params, $queue = 'send_email')
    {
        $this->onQueue($queue);
        $this->params = $params;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $driver = "";
        if (config('v2board.email_host')) {
            $driver = "SMTP";
            Config::set('mail.host', config('v2board.email_host', env('mail.host')));
            Config::set('mail.port', config('v2board.email_port', env('mail.port')));
            Config::set('mail.encryption', config('v2board.email_encryption', env('mail.encryption')));
            Config::set('mail.username', config('v2board.email_username', env('mail.username')));
            Config::set('mail.password', config('v2board.email_password', env('mail.password')));
        } elseif (config('v2board.email_postal_host')) {
            $driver = "Postal";
        }
        Config::set('mail.from.address', config('v2board.email_from_address', env('mail.from.address')));
        Config::set('mail.from.name', config('v2board.app_name', 'V2Board'));
        $params = $this->params;
        $email = $params['email'];
        $subject = $params['subject'];
        $params['template_name'] = 'mail.' . config('v2board.email_template', 'default') . '.' . $params['template_name'];
        try {
            switch ($driver) {
                case 'SMTP':
                    Mail::send(
                        $params['template_name'],
                        $params['template_value'],
                        function ($message) use ($email, $subject) {
                            $message->to($email)->subject($subject);
                        }
                    );
                    break;
                case 'Postal':
                    $senderName = Config::get('mail.from.name');
                    $senderAddress = Config::get('mail.from.address');
                    $client = new Client(config('v2board.email_postal_host'), config('v2board.email_postal_key'));
                    $message = new Message();
                    $message->to($email);
                    $message->from("$senderName <$senderAddress>");
                    $message->sender($senderAddress);
                    $message->subject($subject);
                    $message->htmlBody(view($params['template_name'], $params['template_value'])->render());
                    $client->send->message($message);
                    break;
                default:
                    break;
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $log = [
            'email' => $params['email'],
            'subject' => $params['subject'],
            'template_name' => $params['template_name'],
            'error' => $error ?? NULL
        ];

        MailLog::create($log);
        $log['config'] = config('mail');
        return $log;
    }
}
