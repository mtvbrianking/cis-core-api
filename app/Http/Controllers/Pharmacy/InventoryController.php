<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Inventories.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', [Inventory::class]);

        $user = Auth::guard('api')->user();

        $this->validate($request, [
            'store_id' => [
                'required',
                Rule::exists('pharm_store_user', 'store_id')
                    ->where(function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    }),
            ],
        ]);

        $query = Inventory::where('store_id', $request->store_id);

        $limit = $request->input('limit', 10);

        return response($query->paginate($limit));
    }

    /**
     * Temporarily delete (ban) the specific product.
     *
     * @param string $inventoryId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function revoke($inventoryId)
    {
        $this->authorize('softDelete', [Inventory::class]);

        $user = Auth::guard('api')->user();

        $inventory = Inventory::query()
            ->join('pharm_store_user', 'pharm_store_user.store_id', '=', 'pharm_inventories.store_id')
            ->where('pharm_inventories.id', $inventoryId)
            ->where('pharm_store_user.user_id', $user->id)
            ->with(['store', 'product'])
            ->firstOrFail();

        $inventory->delete();

        $inventory->refresh();

        return response($inventory);
    }

    /**
     * Restore the specific banned product.
     *
     * @param string $inventoryId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function restore($inventoryId)
    {
        $this->authorize('restore', [Inventory::class]);

        $user = Auth::guard('api')->user();

        $inventory = Inventory::query()
            ->join('pharm_store_user', 'pharm_store_user.store_id', '=', 'pharm_inventories.store_id')
            ->where('pharm_inventories.id', $inventoryId)
            ->where('pharm_store_user.user_id', $user->id)
            ->onlyTrashed()
            ->with(['store', 'product'])
            ->firstOrFail();

        $inventory->restore();

        $inventory->refresh();

        return response($inventory);
    }

    /**
     * Permanently delete the specific product.
     *
     * @param string $inventoryId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($inventoryId)
    {
        $this->authorize('forceDelete', [Inventory::class]);

        $user = Auth::guard('api')->user();

        $inventory = Inventory::query()
            ->join('pharm_store_user', 'pharm_store_user.store_id', '=', 'pharm_inventories.store_id')
            ->where('pharm_inventories.id', $inventoryId)
            ->where('pharm_store_user.user_id', $user->id)
            ->onlyTrashed()
            ->firstOrFail();

        $inventory->forceDelete();

        return response(null, 204);
    }
}
