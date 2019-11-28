<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')
    ->group(function () {
        // Admin
        Route::prefix('admin')
            ->middleware('admin')
            ->group(function () {
                // Config
                Route::get('config', 'Admin\\ConfigController@index');
                Route::post('config/save', 'Admin\\ConfigController@save');
                // Plan
                Route::get('plan', 'Admin\\PlanController@index');
                Route::post('plan/save', 'Admin\\PlanController@save');
                Route::post('plan/drop', 'Admin\\PlanController@drop');
                Route::post('plan/update', 'Admin\\PlanController@update');
                // Server
                Route::get('server', 'Admin\\ServerController@index');
                Route::post('server/save', 'Admin\\ServerController@save');
                Route::get('server/group', 'Admin\\ServerController@group');
                Route::post('server/group/save', 'Admin\\ServerController@groupSave');
                Route::post('server/group/drop', 'Admin\\ServerController@groupDrop');
                Route::post('server/drop', 'Admin\\ServerController@drop');
                Route::post('server/update', 'Admin\\ServerController@update');
                // Order
                Route::get('order', 'Admin\\OrderController@index');
                Route::post('order/repair', 'Admin\\OrderController@repair');
                // User
                Route::get('user', 'Admin\\UserController@index');
                Route::post('user/update', 'Admin\\UserController@update');
            });
        // User
        Route::prefix('user')
            ->middleware('user')
            ->group(function () {
                // User
                Route::get('resetUUID', 'UserController@resetUUID');
                Route::get('dashboard', 'UserController@dashboard');
                Route::get('subscribe', 'UserController@subscribe');
                Route::get('logout', 'UserController@logout');
                Route::get('info', 'UserController@info');
                Route::post('changePassword', 'UserController@changePassword');
                Route::post('update', 'UserController@update');
                // Order
                Route::get('order', 'OrderController@index');
                Route::post('order/save', 'OrderController@save');
                Route::post('order/checkout', 'OrderController@checkout');
                Route::get('order/check', 'OrderController@check');
                Route::get('order/details', 'OrderController@details');
                Route::get('order/getPaymentMethod', 'OrderController@getPaymentMethod');
                // Plan
                Route::post('plan/info', 'PlanController@info');
                // Invite
                Route::get('invite', 'InviteController@index');
                Route::get('invite/save', 'InviteController@save');
                Route::get('invite/details', 'InviteController@details');
                // Tutorial
                Route::get('tutorial/getSubscribeUrl', 'TutorialController@getSubscribeUrl');
                Route::get('tutorial/getAppleID', 'TutorialController@getAppleID');
            });

        // Passport
        Route::prefix('passport')
            ->group(function () {
                // Register
                Route::post('register', 'Passport\\RegisterController@index');
                // Comm
                Route::get('config', 'Passport\\CommController@config');
                Route::post('sendEmailVerify', 'Passport\\CommController@sendEmailVerify');
                // Login
                Route::post('login', 'Passport\\LoginController@index');
            });
        // No Auth
        Route::prefix('guest')
            ->group(function () {
                // Plan
                Route::get('plan', 'Guest\\PlanController@index');
                // Order
                Route::post('order/alipayNotify', 'Guest\\OrderController@alipayNotify');
                Route::post('order/stripeNotify', 'Guest\\OrderController@stripeNotify');
                Route::get('order/stripeReturn', 'Guest\\OrderController@stripeReturn');
            });
        // Client
        Route::prefix('client')
            ->middleware('client')
            ->group(function () {
                // Client
                Route::get('subscribe', 'ClientController@subscribe');
            });
        // Server
        Route::prefix('server')
            ->group(function () {
                // Deepbwork
                Route::get('deepbwork/user', 'Server\\DeepbworkController@user');
                Route::get('deepbwork/config', 'Server\\DeepbworkController@config');
                Route::post('deepbwork/submit', 'Server\\DeepbworkController@submit');
            });
    });