<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\MailSend;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Jobs\SendEmail;

class MailController extends Controller
{
    public function send(MailSend $request)
    {

        switch ($request->input('type')) {
            case 1: $users = $this->getAllUser();
            break;
            case 2: $users = $this->getReceiver($request->input('receiver'));
            break;
            case 3: $users = $this->getSubscribeUser();
            break;
            case 4: $users = $this->getExpireUser();
            break;
        }

        foreach ($users as $user) {
            SendEmail::dispatch([
                'email' => $user->email,
                'subject' => $request->input('subject'),
                'template_name' => 'mail.sendEmailCustom',
                'template_value' => [
                    'name' => config('v2board.app_name', 'V2Board'),
                    'url' => config('v2board.app_url'),
                    'content' => $request->input('content')
                ]
            ])->onQueue('other_mail');
        }

        return response([
            'data' => true
        ]);
    }

    private function getAllUser()
    {
        return User::all();
    }

    private function getReceiver($receiver)
    {
        if (empty($receiver)) {
            abort(500, '收件人不能为空');
        }
        return User::whereIn('id', $receiver)->get();
    }

    private function getSubscribeUser()
    {
        return User::where('expired_at', '=>', time())->get();
    }

    private function getExpireUser()
    {
        return User::where('expired_at', '<', time())->get();
    }
}
