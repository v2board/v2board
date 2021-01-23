<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected $staticRoutes = ['ClientRoute', 'ServerRoute'];

    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();

        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::group([
            'prefix' => '/api/v1',
            'middleware' => 'api',
            'namespace' => $this->namespace
        ], function ($router) {
            foreach (glob(app_path('Http//Routes') . '/*.php') as $file) {
                if (!in_array(($r = basename($file, '.php')), $this->staticRoutes)) {
                    $this->app->make('App\\Http\\Routes\\' . $r)->map($router);
                }
            }
        });
    }

    protected function mapStaticRoutes()
    {
        Route::group([
            'prefix' => '/api/v1',
            'middleware' => 'static',
            'namespace' => $this->namespace
        ], function ($router) {
            foreach ($this->staticRoutes as $r) {
                $this->app->make('App\\Http\\Routes\\' . $r)->map($router);
            }
        });
    }
}
