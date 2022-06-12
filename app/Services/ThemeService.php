<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class ThemeService
{
    private $path;
    private $theme;

    public function __construct($theme)
    {
        $this->theme = $theme;
        $this->path = $path = public_path('theme/');
    }

    public function init()
    {
        $themeConfigFile = $this->path . "{$this->theme}/config.php";
        if (!File::exists($themeConfigFile)) return;
        $themeConfig = include($themeConfigFile);
        $configs = $themeConfig['configs'];
        $data = [];
        foreach ($configs as $config) {
            $data[$config['field_name']] = isset($config['default_value']) ? $config['default_value'] : '';
        }

        $data = var_export($data, 1);
        try {
            if (!File::put(base_path() . "/config/theme/{$this->theme}.php", "<?php\n return $data ;")) {
                abort(500, "{$this->theme}初始化失败");
            }
        } catch (\Exception $e) {
            abort(500, '请检查V2Board目录权限');
        }

        try {
            Artisan::call('config:cache');
            while (true) {
                if (config("theme.{$this->theme}")) break;
            }
        } catch (\Exception $e) {
            abort(500, "{$this->theme}初始化失败");
        }
    }
}
