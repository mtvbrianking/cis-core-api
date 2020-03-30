<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BatchController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Batches.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', [Batch::class]);

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

        $query = Batch::where('store_id', $request->store_id);

        $limit = $request->input('limit', 10);

        return response($query->paginate($limit));
    }

    /**
     * Create a batch.
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
        $this->authorize('create', [Batch::class]);

        $user = Auth::guard('api')->user();

        $this->validate($request, [
            'store_id' => [
                'required',
                Rule::exists('pharm_stores', 'id')->where(function ($query) use ($user) {
                    $query->where('facility_id', $user->facility_id);
                }),
            ],
            'product_id' => [
                'required',
                Rule::exists('pharm_products', 'id')->where(function ($query) use ($user) {
                    $query->where('facility_id', $user->facility_id);
                }),
            ],
            'quantity' => 'required|integer',
            'unit_price' => 'required|numeric',
            'mfr_batch_no' => 'nullable|max:255',
            'mfd_at' => 'nullable|date_format:Y-m-d',
            'expires_at' => 'nullable|date_format:Y-m-d',
        ]);

        $batch = new Batch();
        $batch->store_id = $request->store_id;
        $batch->product_id = $request->product_id;
        $batch->quantity = $request->quantity;
        $batch->unit_price = $request->unit_price;
        $batch->mfr_batch_no = $request->mfr_batch_no;
        $batch->mfd_at = $request->mfd_at;
        $batch->expires_at = $request->expires_at;
        $batch->save();

        // $batch->refresh();
        $batch = $batch->fresh(['store', 'product']);

        return response($batch, 201);
    }

    /**
     * Permanently delete the specific batch.
     *
     * @param string $batchId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($batchId)
    {
        $this->authorize('delete', [Batch::class]);

        $batch = Batch::findOrFail($batchId);

        $user = Auth::guard('api')->user();

        if (! $user->pharm_stores()->wherePivot('store_id', $batch->store_id)->exists()) {
            return response(['message' => "Batch doesn't belong to any of your stores."], 403);
        }

        $batch->delete();

        return response(null, 204);
    }
}
