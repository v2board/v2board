<?php

namespace App\Http\Controllers\Passport;

use App\Http\Requests\Passport\CommSendEmailVerify;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Mail;
use App\Utils\Helper;
use Illuminate\Support\Facades\Cache;
use App\Jobs\SendEmail;
use App\Models\InviteCode;
use App\Utils\Dict;

class CommController extends Controller
{
    public function config()
    {
        return response([
            'data' => [
                'isEmailVerify' => (int)config('v2board.email_verify', 0) ? 1 : 0,
                'isInviteForce' => (int)config('v2board.invite_force', 0) ? 1 : 0
            ]
        ]);
    }

    private function isEmailVerify()
    {
        return response([
            'data' => (int)config('v2board.email_verify', 0) ? 1 : 0
        ]);
    }

    public function sendEmailVerify(CommSendEmailVerify $request)
    {
        $email = $request->input('email');
        $cacheKey = 'sendEmailVerify:' . $email;
        if (Cache::get($cacheKey)) {
            abort(500, '验证码已发送，请过一会在请求');
        }
        $code = Helper::randomChar(6);
        $subject = config('v2board.app_name', 'V2Board') . '邮箱验证码';

        SendEmail::dispatch([
            'email' => $email,
            'subject' => $subject,
            'template_name' => 'mail.sendEmailVerify',
            'template_value' => [
                'name' => config('v2board.app_name', 'V2Board'),
                'code' => $code,
                'url' => config('v2board.app_url')
            ]
        ])->onQueue('verify_mail');

        Cache::put($cacheKey, $code, 60);
        return response([
            'data' => true
        ]);
    }

    public function getEmailSuffix()
    {
        return response([
            'data' => config('v2board.email_suffix_whitelist', Dict::EMAIL_WHITELIST_SUFFIX_DEFAULT)
        ]);
    }

    public function pv(Request $request)
    {
        $inviteCode = InviteCode::where('code', $request->input('invite_code'))->first();
        if ($inviteCode) {
            $inviteCode->pv = $inviteCode->pv + 1;
            $inviteCode->save();
        }

        return response([
            'data' => true
        ]);
    }
}
