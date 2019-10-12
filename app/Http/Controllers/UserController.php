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
use Illuminate\Auth\Access\AuthorizationException;

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
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('viewAny', [User::class]);

        $consumer = Auth::guard('api')->user();

        $users = User::onlyRelated($consumer)->withTrashed()->get();

        return response(['users' => $users]);
    }

    /**
     * Get specific user.
     *
     * @param string $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->authorize('view', [User::class, $id]);

        $consumer = Auth::guard('api')->user();

        $user = User::with(['role', 'facility'])
            ->onlyRelated($consumer)
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
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', [User::class]);

        $this->validate($request, [
            'name' => 'required|max:25',
            'alias' => 'required|unique:users,alias',
            'email' => 'required|unique:users,email',
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
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->authorize('update', [User::class, $id]);

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
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function revoke($id)
    {
        $this->authorize('softDelete', [User::class, $id]);

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
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        $this->authorize('restore', [User::class, $id]);

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
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->authorize('forceDelete', [User::class, $id]);

        $user = Auth::guard('api')->user();

        $user = User::onlyRelated($user)->onlyTrashed()->findOrFail($id);

        $user->forceDelete();

        return response(null, 204);
    }

    /**
     * Determine this is your valid account password.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $id
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function confirmPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $consumer = Auth::guard('api')->user();

        if ($consumer->id != $user->id) {
            throw new AuthorizationException("Can't confirm someone else's password.");
        }

        $this->validate($request, [
            'password' => 'required',
        ]);

        if (! password_verify($request->password, $user->password)) {
            $validator = Validator::make([], []);
            $validator->errors()->add('password', 'Wrong password.');

            throw new ValidationException($validator);
        }

        return response(null, 204);
    }

    /**
     * Change your account password.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $consumer = Auth::guard('api')->user();

        if ($consumer->id != $user->id) {
            throw new AuthorizationException("Can't update someone else's password.");
        }

        $this->validate($request, [
            'password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        if (! password_verify($request->password, $user->password)) {
            $validator = Validator::make([], []);
            $validator->errors()->add('password', 'Wrong password.');

            throw new ValidationException($validator);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response(null, 204);
    }

    /**
     * Determine if a user exists with given email.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return \Illuminate\Http\Response
     */
    public function validateEmail(Request $request)
    {
        // Client credentials grant <-

        $this->validate($request, [
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        return response(null, 204);
    }

    /**
     * Mark that a user has confirmed their email address.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return \Illuminate\Http\Response
     */
    public function confirmEmail(Request $request)
    {
        // Client credentials grant <-

        $this->validate($request, [
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            $validator = Validator::make([], []);
            $validator->errors()->add('email', 'Wrong email address.');

            throw new ValidationException($validator);
        }

        $user->email_verified_at = date('Y-m-d H:i:s');
        $user->save();

        return response(null, 204);
    }

    /**
     * Reset your forgotten password.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return \Illuminate\Http\Response
     */
    public function resetPassword(Request $request)
    {
        // Client credentials grant <-

        $this->validate($request, [
            'email' => 'required|email',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            $validator = Validator::make([], []);
            $validator->errors()->add('email', 'Wrong email address.');

            throw new ValidationException($validator);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response(null, 204);
    }
}
