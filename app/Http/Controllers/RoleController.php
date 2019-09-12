<?php

namespace App\Http\Controllers;

use App\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get roles.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        //
    }

    /**
     * Register new role.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Get specific role.
     *
     * @param string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        //
    }

    /**
     * Update specific role.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Temporarily delete (ban) the specific role.
     *
     * @param string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function trash($id)
    {
        //
    }

    /**
     * Restore the specific banned role.
     *
     * @param string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id)
    {
        //
    }

    /**
     * Permanently delete the specific role.
     *
     * @param string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Get users having this role.
     *
     * @param string $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function users($id)
    {
        //
    }

    /**
     * All permission available to this role.
     *
     * @param string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function permissions($id)
    {
        //
    }

    /**
     * Only permissions granted to this role.
     *
     * @param string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function permissions_granted($id)
    {
        //
    }

    /**
     * Sync role permissions.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sync_permissions(Request $request, $id)
    {
        //
    }
}
