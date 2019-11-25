<?php

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

Route::get('/', function () {
    return view('app', [
        'title' => config('v2board.app_name', 'V2Board'),
        'theme' => config('v2board.app_theme', 1),
        'verison' => '0.1.6'
    ]);
});
