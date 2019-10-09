<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

        $user = User::onlyRelated($user)->withTrashed()->findOrFail($id);

        return response($user);
    }

    /**
     * Register new user.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:25',
            'alias' => 'required|unique:users,alias',
            'email' => 'required|unique:email,alias',
            'role' => 'nullable|uuid',
        ]);

        $creator = Auth::guard('api')->user();

        $role = Role::onlyRelated($creator)->find($request->role);

        if (! $role) {
            $validator = Validator::make([], []);
            $validator->errors()->add('role', 'Unknown role.');

            throw new ValidationException($validator);
        }

        $user = new User();
        $user->name = $request->name;
        $user->alias = $request->alias;
        $user->email = $request->email;
        $user->creator()->associate($creator);
        $user->facility()->associate($creator->facility);
        $user->role()->associate($role);
        $user->save();

        // Create UserRegistered event

        $user->refresh();

        return response($user, 201);
    }

    /**
     * Update specific user.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $id
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
            'role' => 'nullable|uuid',
        ]);

        if ($request->filled('role')) {
            $role = Role::onlyRelated($creator)->find($request->role);

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
