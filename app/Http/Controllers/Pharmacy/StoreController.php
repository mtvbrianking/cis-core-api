<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Stores.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', [Store::class]);

        $user = Auth::guard('api')->user();

        $query = Store::onlyRelated($user)->withTrashed();

        $limit = $request->input('limit', 10);

        if ($request->input('paginate', true)) {
            return response($query->paginate($limit), 206);
        }

        $stores = $query->take($limit)->get();

        return response(['stores' => $stores]);
    }

    /**
     * Create a store.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', [Store::class]);

        $this->validate($request, [
            'name' => 'required|max:100',
        ]);

        $user = Auth::guard('api')->user();

        $store = new Store();
        $store->name = $request->name;
        $store->facility()->associate($user->facility);
        $store->save();

        $store->refresh();

        return response($store, 201);
    }

    /**
     * Store details.
     *
     * @param string $storeId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     *
     * @return \Illuminate\Http\Response
     */
    public function show($storeId)
    {
        $this->authorize('view', [Store::class, $storeId]);

        $user = Auth::guard('api')->user();

        $store = Store::onlyRelated($user)->withTrashed()->findOrFail($storeId);

        return response($store);
    }

    /**
     * Update the specified store in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $storeId
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $storeId)
    {
        $this->authorize('update', [Store::class]);

        $user = Auth::guard('api')->user();

        $store = Store::onlyRelated($user)->findOrFail($storeId);

        $this->validate($request, [
            'name' => 'required|max:100',
        ]);

        $store->name = $request->name;
        $store->save();

        $store = Store::with('facility')->find($storeId);

        return response($store);
    }

    /**
     * Temporarily delete (ban) the specific store.
     *
     * @param string $storeId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     *
     * @return \Illuminate\Http\Response
     */
    public function revoke($storeId)
    {
        $this->authorize('softDelete', [Store::class]);

        $store = Store::findOrFail($storeId);

        $store->delete();

        $store->refresh();

        return response($store);
    }

    /**
     * Restore the specific banned store.
     *
     * @param string $storeId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     *
     * @return \Illuminate\Http\Response
     */
    public function restore($storeId)
    {
        $this->authorize('restore', [Store::class]);

        $store = Store::onlyTrashed()->findOrFail($storeId);

        $store->restore();

        $store->refresh();

        return response($store);
    }

    /**
     * Permanently delete the specific store.
     *
     * @param string $storeId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($storeId)
    {
        $this->authorize('forceDelete', [Store::class]);

        Store::onlyTrashed()->findOrFail($storeId)->forceDelete();

        return response(null, 204);
    }
}
