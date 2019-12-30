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
                Route::get ('config/fetch', 'Admin\\ConfigController@fetch');
                Route::post('config/save', 'Admin\\ConfigController@save');
                // Plan
                Route::get ('plan/fetch', 'Admin\\PlanController@fetch');
                Route::post('plan/save', 'Admin\\PlanController@save');
                Route::post('plan/drop', 'Admin\\PlanController@drop');
                Route::post('plan/update', 'Admin\\PlanController@update');
                // Server
                Route::get ('server/fetch', 'Admin\\ServerController@fetch');
                Route::post('server/save', 'Admin\\ServerController@save');
                Route::get ('server/group/fetch', 'Admin\\ServerController@groupFetch');
                Route::post('server/group/save', 'Admin\\ServerController@groupSave');
                Route::post('server/group/drop', 'Admin\\ServerController@groupDrop');
                Route::post('server/drop', 'Admin\\ServerController@drop');
                Route::post('server/update', 'Admin\\ServerController@update');
                // Order
                Route::get ('order/fetch', 'Admin\\OrderController@fetch');
                Route::post('order/repair', 'Admin\\OrderController@repair');
                Route::post('order/update', 'Admin\\OrderController@update');
                // User
                Route::get ('user/fetch', 'Admin\\UserController@fetch');
                Route::post('user/update', 'Admin\\UserController@update');
                Route::get ('user/id2UserInfo/{id}', 'Admin\\UserController@id2UserInfo');
                // Stat
                Route::get ('stat/getOverride', 'Admin\\StatController@getOverride');
                // Notice
                Route::get ('notice/fetch', 'Admin\\NoticeController@fetch');
                Route::post('notice/save', 'Admin\\NoticeController@save');
                Route::post('notice/update', 'Admin\\NoticeController@update');
                Route::post('notice/drop', 'Admin\\NoticeController@drop');
                // Ticket
                Route::get ('ticket/fetch', 'Admin\\TicketController@fetch');
                Route::post('ticket/reply', 'Admin\\TicketController@reply');
                Route::post('ticket/close', 'Admin\\TicketController@close');
                // Mail
                Route::post('mail/send', 'Admin\\MailController@send');
            });
        // User
        Route::prefix('user')
            ->middleware('user')
            ->group(function () {
                // User
                Route::get ('resetSecurity', 'UserController@resetSecurity');
                Route::get ('logout', 'UserController@logout');
                Route::get ('info', 'UserController@info');
                Route::post('changePassword', 'UserController@changePassword');
                Route::post('update', 'UserController@update');
                Route::get ('getSubscribe', 'UserController@getSubscribe');
                Route::get ('getStat', 'UserController@getStat');
                // Order
                Route::post('order/save', 'OrderController@save');
                Route::post('order/checkout', 'OrderController@checkout');
                Route::get ('order/check', 'OrderController@check');
                Route::get ('order/details', 'OrderController@details');
                Route::get ('order/fetch', 'OrderController@fetch');
                Route::get ('order/getPaymentMethod', 'OrderController@getPaymentMethod');
                // Plan
                Route::post('plan/fetch', 'PlanController@fetch');
                // Invite
                Route::get ('invite/save', 'InviteController@save');
                Route::get ('invite/fetch', 'InviteController@fetch');
                Route::get ('invite/details', 'InviteController@details');
                // Tutorial
                Route::get ('tutorial/getSubscribeUrl', 'TutorialController@getSubscribeUrl');
                Route::get ('tutorial/getAppleID', 'TutorialController@getAppleID');
                // Notice
                Route::get ('notice/fetch', 'NoticeController@fetch');
                // Ticket
                Route::post('ticket/reply', 'TicketController@reply');
                Route::post('ticket/close', 'TicketController@close');
                Route::post('ticket/save', 'TicketController@save');
                Route::get ('ticket/fetch', 'TicketController@fetch');
                // Server
                Route::get ('server/fetch', 'ServerController@fetch');
                Route::get ('server/log/fetch', 'ServerController@logFetch');
            });

        // Passport
        Route::prefix('passport')
            ->group(function () {
                // Register
                Route::post('register', 'Passport\\RegisterController@index');
                // Comm
                Route::get ('config', 'Passport\\CommController@config');
                Route::post('sendEmailVerify', 'Passport\\CommController@sendEmailVerify');
                // Login
                Route::post('login', 'Passport\\LoginController@index');
                Route::get ('token2Login', 'Passport\\LoginController@token2Login');
                Route::get ('check', 'Passport\\LoginController@check');
                // Forget
                Route::post('forget', 'Passport\\ForgetController@index');
            });
        // No Auth
        Route::prefix('guest')
            ->group(function () {
                // Plan
                Route::get ('plan/fetch', 'Guest\\PlanController@fetch');
                // Order
                Route::post('order/alipayNotify', 'Guest\\OrderController@alipayNotify');
                Route::post('order/stripeNotify', 'Guest\\OrderController@stripeNotify');
                Route::post('order/bitpayXNotify', 'Guest\\OrderController@bixpayXNotify');
            });
        // Client
        Route::prefix('client')
            ->middleware('client')
            ->group(function () {
                // Client
                Route::get ('subscribe', 'ClientController@subscribe');
                // App
                Route::get ('app/data', 'AppController@data');
                Route::get ('app/config', 'AppController@config');
            });
        // Server
        Route::prefix('server')
            ->group(function () {
                // Deepbwork
                Route::get ('deepbwork/user', 'Server\\DeepbworkController@user');
                Route::get ('deepbwork/config', 'Server\\DeepbworkController@config');
                Route::post('deepbwork/submit', 'Server\\DeepbworkController@submit');
            });
    });