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
        if ($request->input('type') == 2 && empty($request->input('receiver'))) {
            abort(500, '收件人不能为空');
        }

        if ($request->input('receiver')) {
            $users = User::whereIn('id', $request->input('receiver'))->get();
        } else {
            $users = User::all();
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
}
