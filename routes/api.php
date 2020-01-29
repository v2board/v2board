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
            	Route::any('/{class}/{action}', function($class, $action) {
				    $ctrl = \App::make("\\App\\Http\\Controllers\\Admin\\" . ucfirst($class) . "Controller");
                    return \App::call([$ctrl, $action]);
				});
            });
        // User
        Route::prefix('user')
            ->middleware('user')
            ->group(function () {
            	Route::any('/{action}', function($action) {
				    $ctrl = \App::make("\\App\\Http\\Controllers\\User\\UserController");
				    return \App::call([$ctrl, $action]);
				});
            	Route::any('/{class}/{action}', function($class, $action) {
				    $ctrl = \App::make("\\App\\Http\\Controllers\\User\\" . ucfirst($class) . "Controller");
				    return \App::call([$ctrl, $action]);
				});
                Route::get('server/log/fetch', 'User\\ServerController@logFetch');
            });

        // Passport
        Route::prefix('passport')
            ->group(function () {
            	Route::any('/{class}/{action}', function($class, $action) {
				    $ctrl = \App::make("\\App\\Http\\Controllers\\Passport\\" . ucfirst($class) . "Controller");
				    return \App::call([$ctrl, $action]);
				});
            });
        // No Auth
        Route::prefix('guest')
            ->group(function () {
            	Route::any('/{class}/{action}', function($class, $action) {
				    $ctrl = \App::make("\\App\\Http\\Controllers\\Guest\\" . ucfirst($class) . "Controller");
				    return \App::call([$ctrl, $action]);
				});
            });
        // Client
        Route::prefix('client')
            ->middleware('client')
            ->group(function () {
            	Route::any('/{action}', function($action) {
				    $ctrl = \App::make("\\App\\Http\\Controllers\\Client\\ClientController");
				    return \App::call([$ctrl, $action]);
				});
            	Route::any('/{class}/{action}', function($class, $action) {
				    $ctrl = \App::make("\\App\\Http\\Controllers\\Client\\" . ucfirst($class) . "Controller");
				    return \App::call([$ctrl, $action]);
				});
            });
        // Server
        Route::prefix('server')
            ->group(function () {
            	Route::any('/{class}/{action}', function($class, $action) {
				    $ctrl = \App::make("\\App\\Http\\Controllers\\Server\\" . ucfirst($class) . "Controller");
				    return \App::call([$ctrl, $action]);
				});
            });
    });
