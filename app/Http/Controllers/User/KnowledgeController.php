<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use App\Utils\Helper;
use Illuminate\Http\Request;
use App\Models\Knowledge;

class KnowledgeController extends Controller
{
    public function fetch(Request $request)
    {
        if ($request->input('id')) {
            $knowledge = Knowledge::where('id', $request->input('id'))
                ->where('show', 1)
                ->first()
                ->toArray();
            if (!$knowledge) abort(500, __('Article does not exist'));
            $user = User::find($request->user['id']);
            $userService = new UserService();
            if (!$userService->isAvailable($user)) {
                $this->formatAccessData($knowledge['body']);
            }
            $subscribeUrl = Helper::getSubscribeUrl("/api/v1/client/subscribe?token={$user['token']}");
            $knowledge['body'] = str_replace('{{siteName}}', config('v2board.app_name', 'V2Board'), $knowledge['body']);
            $knowledge['body'] = str_replace('{{subscribeUrl}}', $subscribeUrl, $knowledge['body']);
            $knowledge['body'] = str_replace('{{urlEncodeSubscribeUrl}}', urlencode($subscribeUrl), $knowledge['body']);
            $knowledge['body'] = str_replace(
                '{{safeBase64SubscribeUrl}}',
                str_replace(
                    array('+', '/', '='),
                    array('-', '_', ''),
                    base64_encode($subscribeUrl)
                ),
                $knowledge['body']
            );
            $this->apple($knowledge['body']);
            return response([
                'data' => $knowledge
            ]);
        }
        $builder = Knowledge::select(['id', 'category', 'title', 'updated_at'])
            ->where('language', $request->input('language'))
            ->where('show', 1)
            ->orderBy('sort', 'ASC');
        $keyword = $request->input('keyword');
        if ($keyword) {
            $builder = $builder->where(function ($query) use ($keyword) {
                $query->where('title', 'LIKE', "%{$keyword}%")
                    ->orWhere('body', 'LIKE', "%{$keyword}%");
            });
        }

        $knowledges = $builder->get()
            ->groupBy('category');
        return response([
            'data' => $knowledges
        ]);
    }

    private function formatAccessData(&$body)
    {
        function getBetween($input, $start, $end){$substr = substr($input, strlen($start)+strpos($input, $start),(strlen($input) - strpos($input, $end))*(-1));return $start . $substr . $end;}
        while (strpos($body, '<!--access start-->') !== false) {
            $accessData = getBetween($body, '<!--access start-->', '<!--access end-->');
            if ($accessData) {
                $body = str_replace($accessData, '<div class="v2board-no-access">'. __('You must have a valid subscription to view content in this area') .'</div>', $body);
            }
        }
    }

    private function apple(&$body)
    {
        try{
            $req = json_decode($this->api_request_curl('https://yoursite.com'), true);
            $status = ['正在检测', '状态正常', '状态异常', '等待检测'];
            foreach ($req as $k => $v) {
                $body = str_replace("{{ausername$k}}", $v["username"], $body);
                $body = str_replace("{{apassword$k}}", $v["password"], $body);
                $body = str_replace("{{astatus$k}}", $status[$v["status"]], $body);
                $body = str_replace("{{atime$k}}", $v["time"], $body);
            }
        }catch (\Exception $error) {
            foreach (range(0,99) as $k) {
                $body = str_replace("{{ausername$k}}", "获取错误", $body);
                $body = str_replace("{{apassword$k}}", "获取错误", $body);
                $body = str_replace("{{astatus$k}}", "获取错误", $body);
                $body = str_replace("{{atime$k}}", "获取错误", $body);
            }
        }
    }

    private function api_request_curl($url) {
        if (empty($url)) return '';

        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL, $url);
        curl_setopt($curl,CURLOPT_TIMEOUT, 30);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept: application/json, text/plain, */*'
        ));

        $result = curl_exec($curl);
        if($result === false){
            throw new Exception('Http request message :'.curl_error($curl));
        }

        curl_close($curl);
        return $result;
    }

}
