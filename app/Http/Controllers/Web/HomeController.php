<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    /**
     * Show API usage documentation.
     *
     * @return \Illuminate\View\View
     */
    public function showApiUsageDoc()
    {
        return view('api-usage');
    }

    /**
     * Show application routes.
     *
     * @return \Illuminate\View\View
     */
    public function showApplicationRoutes()
    {
        if (config('app.log_level') == 'production') {
            abort(403);
        }

        $routes = collect(Route::getRoutes());

        $routes = $routes->map(function ($route) {
            return [
                'host' => $route->action['where'],
                'uri' => $route->uri,
                'name' => isset($route->action['as']) ? $route->action['as'] : '',
                'methods' => $route->methods,
                'action' => isset($route->action['controller']) ? $route->action['controller'] : 'Closure',
                'middleware' => $this->getRouteMiddleware($route),
                // 'pattern' => $route->wheres,
            ];
        });

        return view('routes', [
            'routes' => $routes,
        ]);
    }

    /**
     * Get route middleware.
     *
     * @param \Illuminate\Routing\Route $route
     *
     * @return string
     */
    protected function getRouteMiddleware($route)
    {
        return collect($route->gatherMiddleware())->map(function ($middleware) {
            return $middleware instanceof \Closure ? 'Closure' : $middleware;
        })->implode(', ');
    }
}
