<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\Inventory;
use Bmatovu\QueryDecorator\Json\Schema;
use Bmatovu\QueryDecorator\Query\Decorator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use JsonSchema\Validator as JsonValidator;

class InventoryController extends Controller
{
    /**
     * Json schema validator.
     *
     * @var \JsonSchema\Validator
     */
    protected $jsonValidator;

    /**
     * Constructor.
     *
     * @param \JsonSchema\Validator $jsonValidator
     */
    public function __construct(JsonValidator $jsonValidator)
    {
        $this->middleware('auth:api');

        $this->jsonValidator = $jsonValidator;
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

        // Validate request query parameters.

        $schemaPath = resource_path('js/schemas/pharmacy/inventories.json');

        Schema::validate($this->jsonValidator, $schemaPath, $request->query());

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

        // Apply constraints to query.

        $query = Decorator::decorate($query, (array) $request->query('filters'));

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
