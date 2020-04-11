<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\Product;
use App\Models\Pharmacy\Purchase;
use App\Models\Pharmacy\Store;
use App\Models\Pharmacy\StoreUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Purchases.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $storeId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $storeId)
    {
        $this->authorize('viewAny', [Purchase::class]);

        $user = Auth::guard('api')->user();

        $store = Store::onlyRelated($user)->findOrFail($storeId);

        $query = Purchase::where('store_id', $store->id);

        return response($query->paginate(), 206);
    }

    /**
     * Purchase with store and products details.
     *
     * @param string $storeId
     * @param string $purchaseId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function show($storeId, $purchaseId)
    {
        $this->authorize('view', [Purchase::class]);

        $user = Auth::guard('api')->user();

        $store = Store::onlyRelated($user)->findOrFail($storeId);

        $purchase = Purchase::with(['store', 'user', 'products'])->where('store_id', $store->id)->findOrFail($purchaseId);

        return response($purchase);
    }

    /**
     * Create a purchase.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $purchaseId
     * @param mixed                    $storeId
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $storeId)
    {
        $this->authorize('create', [Purchase::class]);

        $user = Auth::guard('api')->user();

        StoreUser::where('store_id', $storeId)->where('user_id', $user->id)->firstOrFail();

        $this->validate($request, [
            'products' => 'required|array',
            'products.*.id' => 'required|string|size:11',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.cost_price' => 'required|numeric',
            'products.*.unit_retail_price' => 'required|numeric',
            // 'products.*.unit_wholesale_price' => 'required|numeric',
        ]);

        $requested_product_ids = array_column($request->products, 'id');

        $validProducts = Product::query()
            ->where('facility_id', $user->facility_id)
            ->whereIn('id', $requested_product_ids)
            ->get();

        $errors = [];

        foreach ($request->products as $idx => $reqProduct) {
            $product = $validProducts->where('id', $reqProduct['id'])->first();

            if (! $product) {
                $errors["products.{$idx}.id"][] = 'Unknown product.';

                continue;
            }
        }

        if ($errors) {
            $validator = Validator::make([], []);
            $validator->errors()->merge($errors);

            throw new ValidationException($validator);
        }

        DB::beginTransaction();

        try {
            $products = [];

            $total = 0;

            foreach ($request->products as $product) {
                $storeProduct = DB::table('pharm_store_product')
                    ->where('store_id', $storeId)
                    ->where('product_id', $product['id'])
                    ->first();

                if ($storeProduct) {
                    DB::table('pharm_store_product')
                        ->where('store_id', $storeId)
                        ->where('product_id', $product['id'])
                        ->update([
                            'quantity' => $storeProduct->quantity + $product['quantity'],
                            'unit_price' => $product['unit_retail_price'],
                        ]);
                } else {
                    DB::table('pharm_store_product')
                        ->insert([
                            'store_id' => $storeId,
                            'product_id' => $product['id'],
                            'quantity' => $product['quantity'],
                            'unit_price' => $product['unit_retail_price'],
                        ]);
                }

                $products[$product['id']] = [
                    'quantity' => $product['quantity'],
                    'unit_price' => floatval($product['unit_retail_price']),
                ];

                $total += $product['cost_price'];
            }

            $purchase = new Purchase();
            $purchase->store_id = $storeId;
            $purchase->user_id = $user->id;
            $purchase->total = $total;
            $purchase->save();

            $purchase->products()->attach($products);
            $purchase->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }

        $purchase = $purchase->fresh(['store', 'user', 'products']);

        return response($purchase, 201);
    }
}
