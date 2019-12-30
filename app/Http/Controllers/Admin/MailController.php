<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\MailSend;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Jobs\SendEmail;

class MailController extends Controller
{
    public function send (MailSend $request) {
        if ($request->input('type') == 2 && $request->input('recevicer')) {
            abort(500, '收件人不能为空');
        }

        if ($request->input('recevicer')) {
            $users = User::whereIn('id', $recevicer)->get();
        } else {
            $users = User::all();
        }

        foreach ($users as $user) {
            SendEmail::dispatch([
                'email' => $user->email,
                'subject' => $request->input('subject'),
                'template_name' => 'mail.sendCustom',
                'template_value' => [
                    'name' => config('v2board.app_name', 'V2Board'),
                    'url' => config('v2board.app_url'),
                    'content' => $request->input('content')
                ]
            ]);
        }

        return response([
            'data' => true
        ]);
    }
}