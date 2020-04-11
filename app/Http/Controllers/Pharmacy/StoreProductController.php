<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\Store;
use App\Models\Pharmacy\StoreProduct;
use Bmatovu\QueryDecorator\Json\Schema;
use Bmatovu\QueryDecorator\Query\Decorator;
use Bmatovu\QueryDecorator\Support\Datatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use JsonSchema\Validator as JsonValidator;

class StoreProductController extends Controller
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
     * @param string                   $storeId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Bmatovu\QueryDecorator\Exceptions\InvalidJsonException
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $storeId)
    {
        $this->authorize('viewProducts', [Store::class]);

        $user = Auth::guard('api')->user();

        $store = Store::onlyRelated($user)->findOrFail($storeId);

        // Validate request query parameters.

        $schemaPath = resource_path('js/schemas/pharmacy/store-product.json');

        $constraints = (array) $request->query('filters');

        Schema::validate($this->jsonValidator, $schemaPath, $constraints);

        // ...

        $isRelated = false;

        $query = StoreProduct::where('pharm_store_product.store_id', $storeId);

        if (isset($constraints['select'])) {
            $relations = Decorator::getRelations($constraints['select']);

            $isRelated = (bool) count($relations);

            if (in_array('store', $relations)) {
                $query->leftJoin('pharm_stores', 'pharm_stores.id', '=', 'pharm_store_product.store_id');
            }

            if (in_array('product', $relations)) {
                $query->leftJoin('pharm_products', 'pharm_products.id', '=', 'pharm_store_product.product_id');
            }
        }

        $tableModelMap = [
            'pharm_store_product' => null,
            'pharm_stores' => 'store',
            'pharm_products' => 'product',
        ];

        // Apply constraints to query.

        $query = Decorator::decorate($query, $constraints, $tableModelMap, $isRelated);

        // return response($query->toSql());

        $results = $query->jsonPaginate()->toArray();

        $results['data'] = Decorator::resultsByModel($results['data'], $tableModelMap);

        return response($results, 206);
    }

    /**
     * Get inventories for jQuery datatables.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $storeId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Bmatovu\QueryDecorator\Exceptions\InvalidJsonException
     *
     * @return \Illuminate\Http\Response
     */
    public function datatables(Request $request, $storeId)
    {
        $this->authorize('viewProducts', [Store::class]);

        $user = Auth::guard('api')->user();

        $store = Store::onlyRelated($user)->findOrFail($storeId);

        // Validate request query parameters.

        $schemaPath = resource_path('js/schemas/pharmacy/store-product.json');

        $params = (array) $request->query();

        $constraints = Datatable::buildConstraints($params, 'ilike');

        Schema::validate($this->jsonValidator, $schemaPath, $constraints);

        // ...

        $isRelated = false;

        $query = StoreProduct::where('pharm_store_product.store_id', $storeId);

        if (isset($constraints['select'])) {
            $relations = Decorator::getRelations($constraints['select']);

            $isRelated = (bool) count($relations);

            if (in_array('store', $relations)) {
                $query->leftJoin('pharm_stores', 'pharm_stores.id', '=', 'pharm_store_product.store_id');
            }

            if (in_array('product', $relations)) {
                $query->leftJoin('pharm_products', 'pharm_products.id', '=', 'pharm_store_product.product_id');
            }
        }

        $availableRecords = $query->count();

        $tableModelMap = [
            'pharm_store_product' => null,
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
        ], 206);
    }
}
