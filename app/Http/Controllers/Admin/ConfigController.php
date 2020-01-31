<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ConfigSave;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ConfigController extends Controller
{
    public function init()
    {

    }

    public function fetch()
    {
        return response([
            'data' => config('v2board')
        ]);
    }

    public function save(ConfigSave $request)
    {
        $data = $request->input();
//        $array = \Config::get('v2board');
        foreach ($data as $k => $v) {
            if (!in_array($k, ConfigSave::filter())) {
                abort(500, '参数' . $k . '不在规则内，禁止修改');
            }
            config(['v2board.' . $k => $v]);
        }
//        $data = var_export($array, 1);
//        if (!\File::put(base_path() . '/config/v2board.php', "<?php\n return $data ;")) {
//            abort(500, '修改失败');
//        }
        \Artisan::call('config:cache');
        return response([
            'data' => true
        ]);
    }
}
