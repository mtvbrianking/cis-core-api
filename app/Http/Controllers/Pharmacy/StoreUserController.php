<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class StoreUserController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Query stores assigned to a user.
     *
     * Indicating whether or not a store is granted to that user.
     *
     * @param \App\Models\User $user
     *
     * @return \Illuminate\Support\Collection
     */
    protected function queryPharmacyStores(User $user): Collection
    {
        $query = Store::query();

        $query->leftJoin('pharm_store_user', function ($join) use ($user) {
            $join->on('pharm_stores.id', '=', 'pharm_store_user.store_id');
            $join->where('pharm_store_user.user_id', '=', $user->id);
        });

        $query->select([
            'pharm_stores.id',
            'pharm_stores.name',
            'pharm_store_user.user_id',
        ]);

        $stores = $query->get();

        return $stores->map(function ($store) {
            return [
                'id' => $store->id,
                'name' => $store->name,
                'granted' => ! is_null($store->user_id),
            ];
        });
    }

    /**
     * Stores assigned to this user.
     *
     * @param string $userId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function pharmacyStores($userId)
    {
        $this->authorize('syncStoreUsers', [Store::class]);

        $consumer = Auth::guard('api')->user();

        $user = User::onlyRelated($consumer)->findOrFail($userId);

        $user->stores = $this->queryPharmacyStores($user);

        return response($user);
    }

    /**
     * Sync user stores.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $userId
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function syncPharmacyStores(Request $request, $userId)
    {
        $this->authorize('syncStoreUsers', [Store::class]);

        $this->validate($request, [
            'stores' => 'required|array',
            'stores.*' => 'required|regex:/^[0-9a-fA-F]{11}$/',
        ]);

        // Available stores

        $consumer = Auth::guard('api')->user();

        $user = User::onlyRelated($consumer)->findOrFail($userId);

        $available_stores = Store::select('id')->onlyRelated($consumer)->pluck('id')->toArray();

        $unknown_stores = array_values(array_diff($request->stores, $available_stores));

        if ($unknown_stores) {
            $validator = Validator::make([], []);
            $validator->errors()->add('stores', 'Unknown stores: '.implode(', ', $unknown_stores));

            throw new ValidationException($validator);
        }

        // Sync stores...

        $user->pharm_stores()->sync($request->stores, true);
        $user->save();

        $user->stores = $this->queryPharmacyStores($user);

        return response($user);
    }
}
