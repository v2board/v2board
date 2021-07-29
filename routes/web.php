<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function (Request $request) {
    if (config('v2board.app_url') && config('v2board.safe_mode_enable', 0)) {
        if ($request->server('HTTP_HOST') !== parse_url(config('v2board.app_url'))['host']) {
            abort(403);
        }
    }
    $renderParams = [
        'title' => config('v2board.app_name', 'V2Board'),
        'theme' => config('v2board.frontend_theme', 'v2board'),
        'theme_sidebar' => config('v2board.frontend_theme_sidebar', 'light'),
        'theme_header' => config('v2board.frontend_theme_header', 'dark'),
        'theme_color' => config('v2board.frontend_theme_color', 'default'),
        'backgroun_url' => config('v2board.frontend_background_url'),
        'verison' => config('app.version'),
        'description' => config('v2board.app_description', 'V2Board is best'),
        'crisp_id' => config('v2board.frontend_customer_service_method') === 'crisp' ? config('v2board.frontend_customer_service_id') : ''
    ];
    return view('theme::' . config('v2board.frontend_theme', 'v2board') . '.dashboard', $renderParams);
});

Route::get('/' . config('v2board.frontend_admin_path', 'admin'), function () {
    return view('admin', [
        'title' => config('v2board.app_name', 'V2Board'),
        'theme_sidebar' => config('v2board.frontend_theme_sidebar', 'light'),
        'theme_header' => config('v2board.frontend_theme_header', 'dark'),
        'theme_color' => config('v2board.frontend_theme_color', 'default'),
        'backgroun_url' => config('v2board.frontend_background_url'),
        'verison' => config('app.version')
    ]);
});
