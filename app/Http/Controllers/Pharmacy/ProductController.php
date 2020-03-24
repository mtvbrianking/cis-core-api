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
            return response($query->paginate($limit));
        }

        $products = $query->take($limit)->get();

        return response(['products' => $products]);
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
}
