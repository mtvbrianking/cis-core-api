<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get modules.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        //
    }

    /**
     * Register new module.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Get specific module.
     *
     * @param  string $name
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($name)
    {
        //
    }

    /**
     * Update specific module.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $name
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $name)
    {
        //
    }

    /**
     * Temporarily delete (ban) the specific module.
     *
     * @param  string $name
     * @return \Illuminate\Http\JsonResponse
     */
    public function trash($name)
    {
        //
    }

    /**
     * Restore the specific banned module.
     *
     * @param  string $name
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($name)
    {
        //
    }

    /**
     * Permanently delete the specific module.
     *
     * @param  string $name
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($name)
    {
        //
    }
}
