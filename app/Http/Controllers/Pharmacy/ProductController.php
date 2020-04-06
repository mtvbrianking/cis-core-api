<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Products.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', [Product::class]);

        $user = Auth::guard('api')->user();

        $query = Product::onlyRelated($user)->withTrashed();

        $limit = $request->input('limit', 10);

        if ($request->input('paginate', true)) {
            return response($query->paginate($limit), 206);
        }

        $products = $query->take($limit)->get();

        return response(['products' => $products]);
    }

    /**
     * Product details.
     *
     * @param string $productId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function show($productId)
    {
        $this->authorize('view', [Product::class, $productId]);

        $user = Auth::guard('api')->user();

        $product = Product::with('facility')->onlyRelated($user)->withTrashed()->findOrFail($productId);

        return response($product);
    }

    /**
     * Create a product.
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
        $this->authorize('create', [Product::class]);

        $this->validate($request, [
            'name' => 'required|max:255',
            'brand' => 'required|max:255',
            'manufacturer' => 'nullable|max:255',
            'category' => 'nullable|max:150',
            'concentration' => 'nullable|max:100',
            'package' => 'required|in:tablet,pce,bottle',
            'description' => 'nullable',
        ]);

        $user = Auth::guard('api')->user();

        $product = new Product();
        $product->name = $request->name;
        $product->brand = $request->brand;
        $product->manufacturer = $request->manufacturer;
        $product->category = $request->category;
        $product->concentration = $request->concentration;
        $product->package = $request->package;
        $product->description = $request->description;
        $product->facility()->associate($user->facility);
        $product->save();

        $product->refresh();

        return response($product, 201);
    }

    /**
     * Update the specified product in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $productId
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $productId)
    {
        $this->authorize('update', [Product::class]);

        $user = Auth::guard('api')->user();

        $product = Product::onlyRelated($user)->findOrFail($productId);

        $this->validate($request, [
            'name' => 'required|max:255',
            'brand' => 'required|max:255',
            'manufacturer' => 'nullable|max:255',
            'category' => 'nullable|max:150',
            'concentration' => 'nullable|max:100',
            'package' => 'required|in:tablet,pce,bottle',
            'description' => 'nullable',
        ]);

        $product->name = $request->name;
        $product->brand = $request->brand;
        $product->manufacturer = $request->manufacturer;
        $product->category = $request->category;
        $product->concentration = $request->concentration;
        $product->package = $request->package;
        $product->description = $request->description;
        $product->save();

        $product = Product::with('facility')->find($productId);

        return response($product);
    }

    /**
     * Temporarily delete (ban) the specific product.
     *
     * @param string $productId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function revoke($productId)
    {
        $this->authorize('softDelete', [Product::class]);

        $store = Product::with('facility')->findOrFail($productId);

        $store->delete();

        $store->refresh();

        return response($store);
    }

    /**
     * Restore the specific banned product.
     *
     * @param string $productId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function restore($productId)
    {
        $this->authorize('restore', [Product::class]);

        $store = Product::with('facility')->onlyTrashed()->findOrFail($productId);

        $store->restore();

        $store->refresh();

        return response($store);
    }

    /**
     * Permanently delete the specific product.
     *
     * @param string $productId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($productId)
    {
        $this->authorize('forceDelete', [Product::class]);

        Product::onlyTrashed()->findOrFail($productId)->forceDelete();

        return response(null, 204);
    }
}
