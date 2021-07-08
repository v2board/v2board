<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
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
            $user = User::find($request->session()->get('id'));
            $userService = new UserService();
            if ($userService->isAvailable($user)) {
                $appleId = config('v2board.apple_id');
                $appleIdPassword = config('v2board.apple_id_password');
            } else {
                $appleId = __('No active subscription. Unable to use our provided Apple ID');
                $appleIdPassword = __('No active subscription. Unable to use our provided Apple ID');
                $this->formatAccessData($knowledge['body']);
            }
            $subscribeUrl = config('v2board.app_url', env('APP_URL'));
            $subscribeUrls = explode(',', config('v2board.subscribe_url'));
            if ($subscribeUrls) {
                $subscribeUrl = $subscribeUrls[rand(0, count($subscribeUrls) - 1)];
            }
            $subscribeUrl = "{$subscribeUrl}/api/v1/client/subscribe?token={$user['token']}";
            $knowledge['body'] = str_replace('{{siteName}}', config('v2board.app_name', 'V2Board'), $knowledge['body']);
            $knowledge['body'] = str_replace('{{appleId}}', $appleId, $knowledge['body']);
            $knowledge['body'] = str_replace('{{appleIdPassword}}', $appleIdPassword, $knowledge['body']);
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
            return response([
                'data' => $knowledge
            ]);
        }
        $knowledges = Knowledge::select(['id', 'category', 'title', 'updated_at'])
            ->where('language', $request->input('language'))
            ->where('show', 1)
            ->orderBy('sort', 'ASC')
            ->get()
            ->groupBy('category');
        return response([
            'data' => $knowledges
        ]);
    }

    private function formatAccessData(&$body)
    {
        function getBetween($input, $start, $end){$substr = substr($input, strlen($start)+strpos($input, $start),(strlen($input) - strpos($input, $end))*(-1));return $substr;}
        $accessData = getBetween($body, '<!--access start-->', '<!--access end-->');
        if ($accessData) {
            $body = str_replace($accessData, '<div class="v2board-no-access">'. __('You must have a valid subscription to view content in this area') .'</div>', $body);
        }
    }
}
