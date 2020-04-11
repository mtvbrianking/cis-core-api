<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\Inventory;
use Bmatovu\QueryDecorator\Json\Schema;
use Bmatovu\QueryDecorator\Query\Decorator;
use Bmatovu\QueryDecorator\Support\Datatable;
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
     * @throws \Bmatovu\QueryDecorator\Exceptions\InvalidJsonException
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

        // Validate request query parameters.

        $schemaPath = resource_path('js/schemas/pharmacy/inventories.json');

        $constraints = (array) $request->query('filters');

        Schema::validate($this->jsonValidator, $schemaPath, $constraints);

        // ...

        $isRelated = false;

        $query = Inventory::where('pharm_inventories.store_id', $request->store_id);

        if (isset($constraints['select'])) {
            $relations = Decorator::getRelations($constraints['select']);

            $isRelated = (bool) count($relations);

            if (in_array('store', $relations)) {
                $query->leftJoin('pharm_stores', 'pharm_stores.id', '=', 'pharm_inventories.store_id');
            }

            if (in_array('product', $relations)) {
                $query->leftJoin('pharm_products', 'pharm_products.id', '=', 'pharm_inventories.product_id');
            }
        }

        $tableModelMap = [
            'pharm_inventories' => null,
            'pharm_stores' => 'store',
            'pharm_products' => 'product',
        ];

        // Apply constraints to query.

        $query = Decorator::decorate($query, $constraints, $tableModelMap, $isRelated);

        // return response($query->toSql());

        $results = $query->jsonPaginate()->toArray();

        $results['data'] = Decorator::resultsByModel($results['data'], $tableModelMap);

        return response($results);
    }

    /**
     * Get inventories for jQuery datatables.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Bmatovu\QueryDecorator\Exceptions\InvalidJsonException
     *
     * @return \Illuminate\Http\Response
     */
    public function datatables(Request $request)
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

        // Validate request query parameters.

        $schemaPath = resource_path('js/schemas/pharmacy/inventories.json');

        $params = (array) $request->query();

        $constraints = Datatable::buildConstraints($params, 'ilike');

        Schema::validate($this->jsonValidator, $schemaPath, $constraints);

        // ...

        $isRelated = false;

        $query = Inventory::where('pharm_inventories.store_id', $request->store_id);

        if (isset($constraints['select'])) {
            $relations = Decorator::getRelations($constraints['select']);

            $isRelated = (bool) count($relations);

            if (in_array('store', $relations)) {
                $query->leftJoin('pharm_stores', 'pharm_stores.id', '=', 'pharm_inventories.store_id');
            }

            if (in_array('product', $relations)) {
                $query->leftJoin('pharm_products', 'pharm_products.id', '=', 'pharm_inventories.product_id');
            }
        }

        $availableRecords = $query->count();

        $tableModelMap = [
            'pharm_inventories' => null,
            'pharm_stores' => 'store',
            'pharm_products' => 'product',
        ];

        // Apply constraints to query.

        $query = Decorator::decorate($query, $constraints, $tableModelMap, $isRelated);

        $matchedRecords = $query->get();

        // return response($query->toSql());

        $data = Decorator::resultsByModel($matchedRecords, $tableModelMap);

        return response([
            'draw' => (int) $constraints['draw'],
            'recordsTotal' => $availableRecords,
            'recordsFiltered' => isset($constraints['filter']) ? $matchedRecords->count() : $availableRecords,
            'data' => $data,
        ]);
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
