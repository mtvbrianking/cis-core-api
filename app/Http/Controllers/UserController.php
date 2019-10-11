<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get users.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::guard('api')->user();

        $users = User::onlyRelated($user)->withTrashed()->get();

        return response(['users' => $users]);
    }

    /**
     * Get specific user.
     *
     * @param string $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = Auth::guard('api')->user();

        $user = User::with(['role', 'facility'])
            ->onlyRelated($user)
            ->withTrashed()
            ->findOrFail($id);

        return response($user);
    }

    /**
     * Register new user.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:25',
            'alias' => 'required|unique:users,alias',
            'email' => 'required|unique:users,email',
            // 'password' => 'nullable|min:6|confirmed',
            // 'email_verified_at' => 'nullable|date_format:Y-m-d H:i:s',
            'role_id' => 'required|uuid',
        ]);

        $creator = Auth::guard('api')->user();

        $role = Role::onlyRelated($creator)->find($request->role_id);

        if (! $role) {
            $validator = Validator::make([], []);
            $validator->errors()->add('role', 'Unknown role.');

            throw new ValidationException($validator);
        }

        $user = new User();
        $user->name = $request->name;
        $user->alias = $request->alias;
        $user->email = $request->email;
        // $user->email_verified_at = $request->email_verified_at;
        // $user->password = Hash::make($request->input('password', Str::random(10)));
        $user->password = Hash::make(Str::random(10));
        $user->creator()->associate($creator);
        $user->facility()->associate($creator->facility);
        $user->role()->associate($role);
        $user->save();

        $user->refresh();

        return response($user, 201);
    }

    /**
     * Update specific user.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $id
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $creator = Auth::guard('api')->user();

        $user = User::onlyRelated($creator)->findOrFail($id);

        $this->validate($request, [
            'name' => 'sometimes|max:25',
            'alias' => "sometimes|unique:users,alias,{$id},id",
            'email' => "sometimes|unique:users,email,{$id},id",
            'role_id' => 'nullable|uuid',
        ]);

        if ($request->filled('role_id')) {
            $role = Role::onlyRelated($creator)->find($request->role_id);

            if (! $role) {
                $validator = Validator::make([], []);
                $validator->errors()->add('role', 'Unknown role.');

                throw new ValidationException($validator);
            }
        } else {
            $role = $user->role;
        }

        $user->name = $request->input('name', $user->name);
        $user->alias = $request->input('alias', $user->alias);
        if ($request->filled('email') && $request->email != $user->email) {
            $user->email = $request->email;
            $user->email_verified_at = null;
        }
        $user->role()->associate($role);
        $user->save();

        $user->refresh();

        return response($user);
    }

    /**
     * Temporarily delete (ban) the specific user.
     *
     * @param string $id
     *
     * @return \Illuminate\Http\Response
     */
    public function revoke($id)
    {
        $user = Auth::guard('api')->user();

        $user = User::onlyRelated($user)->findOrFail($id);

        $user->delete();

        $user->refresh();

        return response($user);
    }

    /**
     * Restore the specific banned user.
     *
     * @param string $id
     *
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        $user = Auth::guard('api')->user();

        $user = User::onlyRelated($user)->onlyTrashed()->findOrFail($id);

        $user->restore();

        $user->refresh();

        return response($user);
    }

    /**
     * Permanently delete the specific user.
     *
     * @param string $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = Auth::guard('api')->user();

        $user = User::onlyRelated($user)->onlyTrashed()->findOrFail($id);

        $user->forceDelete();

        return response(null, 204);
    }
}
