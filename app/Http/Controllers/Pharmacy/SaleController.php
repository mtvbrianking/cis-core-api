<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\Sale;
use App\Models\Pharmacy\Store;
use App\Models\Pharmacy\StoreProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
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
     * @param \Illuminate\Htpp\Request $request
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', [Sale::class]);

        $user = Auth::guard('api')->user();

        $this->validate($request, [
            'store_id' => [
                'required',
                Rule::exists('pharm_stores', 'id')->where(function ($query) use ($user) {
                    $query->where('facility_id', $user->facility_id);
                }),
            ],
        ]);

        $query = Sale::where('store_id', $request->store_id);

        return response($query->paginate(), 206);
    }

    /**
     * Sale with store and products details.
     *
     * @param string $saleId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     *
     * @return \Illuminate\Http\Response
     */
    public function show($saleId)
    {
        $this->authorize('view', [Sale::class]);

        $user = Auth::guard('api')->user();

        $sale = Sale::with([
            'store' => function ($query) use ($user) {
                $query->where('facility_id', $user->facility_id);
            },
            'user',
            'products',
        ])->findOrFail($saleId);

        return response($sale);
    }

    /**
     * Create a sale.
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
        $this->authorize('create', [Sale::class]);

        $user = Auth::guard('api')->user();

        $this->validate($request, [
            'store_id' => [
                'required',
                Rule::exists('pharm_store_user', 'store_id')->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                }),
            ],
            'products' => 'required|array',
            'products.*.id' => 'required|string|size:11',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        $requested_product_ids = array_column($request->products, 'id');

        $validStoreProducts = StoreProduct::query()
            ->where('store_id', $request->store_id)
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
            $sale->store_id = $request->store_id;
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
