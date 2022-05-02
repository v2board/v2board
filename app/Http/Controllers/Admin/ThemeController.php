<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    public function getThemes()
    {
        $path = public_path('theme/');
        $files = array_map(function ($item) use ($path) {
            return str_replace($path, '', $item);
        }, glob($path . '*'));
        $themeConfigs = [];
        foreach ($files as $file) {
            $themeConfigFile = $path . "{$file}/config.php";
            if (!File::exists($themeConfigFile)) continue;
            $themeConfig = include($themeConfigFile);
            if (!isset($themeConfig['configs']) || !is_array($themeConfig)) continue;
            $themeConfigs[$file] = $themeConfig;
        }
        return response([
            'data' => $themeConfigs
        ]);
    }

    public function saveThemeConfig(Request $request)
    {
        $path = public_path('theme/');
        $files = array_map(function ($item) use ($path) {
            return str_replace($path, '', $item);
        }, glob($path . '*'));
        $payload = $request->validate([
            'name' => 'required|in:' . join(',', $files),
            'configs' => 'required|array'
        ]);
        $themeConfigFile = public_path("theme/{$payload['name']}/config.php");
        if (!File::exists($themeConfigFile)) abort(500, '主题不存在');
        $themeConfig = include($themeConfigFile);
        $validateFields = array_column($themeConfig['configs'], 'field_name');
        dd($validateFields);

    }
}
