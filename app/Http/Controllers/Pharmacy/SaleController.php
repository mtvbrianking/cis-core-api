<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\Sale;
use App\Models\Pharmacy\Store;
use App\Models\Pharmacy\StoreProduct;
use App\Models\Pharmacy\StoreUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Sales belonging to a store.
     *
     * @param string $storeId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function index($storeId)
    {
        $this->authorize('viewAny', [Sale::class]);

        $user = Auth::guard('api')->user();

        $store = Store::onlyRelated($user)->findOrFail($storeId);

        $query = Sale::where('store_id', $store->id);

        return response($query->paginate(), 206);
    }

    /**
     * Sale with store and products details.
     *
     * @param string $storeId
     * @param string $saleId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function show($storeId, $saleId)
    {
        $this->authorize('view', [Sale::class]);

        $user = Auth::guard('api')->user();

        $store = Store::onlyRelated($user)->findOrFail($storeId);

        $sale = Sale::with(['store', 'user', 'products'])->where('store_id', $storeId)->findOrFail($saleId);

        return response($sale);
    }

    /**
     * Create a sale.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $storeId
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $storeId)
    {
        $this->authorize('create', [Sale::class]);

        $user = Auth::guard('api')->user();

        StoreUser::where('store_id', $storeId)->where('user_id', $user->id)->firstOrFail();

        $this->validate($request, [
            'products' => 'required|array',
            'products.*.id' => 'required|string|size:11',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        $requested_product_ids = array_column($request->products, 'id');

        $validStoreProducts = StoreProduct::query()
            ->where('store_id', $storeId)
            ->whereIn('product_id', $requested_product_ids)
            ->get();

        DB::beginTransaction();

        try {
            $errors = $products = [];

            $total = 0;

            foreach ($request->products as $idx => $reqProduct) {
                $storeProduct = $validStoreProducts->where('product_id', $reqProduct['id'])->first();

                if (! $storeProduct) {
                    $errors["products.{$idx}.id"][] = 'Unknown product.';

                    continue;
                }

                if ($storeProduct->quantity < $reqProduct['quantity']) {
                    $errors["products.{$idx}.quantity"][] = "Only {$storeProduct->quantity} in stock.";

                    continue;
                }

                $storeProduct->save();

                DB::table('pharm_store_product')
                    ->where('store_id', $storeProduct->store_id)
                    ->where('product_id', $storeProduct->product_id)
                    ->update([
                        'quantity' => $storeProduct->quantity - $reqProduct['quantity'],
                    ]);

                $products[$storeProduct->product_id] = [
                    'quantity' => $reqProduct['quantity'],
                    'price' => floatval($storeProduct->unit_price),
                ];

                $total += ($storeProduct->unit_price * $reqProduct['quantity']);
            }

            if ($errors) {
                $validator = Validator::make([], []);
                $validator->errors()->merge($errors);

                throw new ValidationException($validator);
            }

            $sale = new Sale();
            $sale->store_id = $storeId;
            $sale->user_id = $user->id;
            $sale->tax_rate = 0.0;
            $sale->total = $total;
            $sale->save();

            $sale->products()->attach($products);
            $sale->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }

        $sale = $sale->fresh(['store', 'user', 'products']);

        return response($sale, 201);
    }
}
