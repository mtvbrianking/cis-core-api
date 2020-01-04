<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Bmatovu\QueryDecorator\Json\Schema;
use Bmatovu\QueryDecorator\Query\Decorator;
use Bmatovu\QueryDecorator\Support\Datatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use JsonSchema\Validator as JsonValidator;

class UserController extends Controller
{
    /**
     * Json schema validator.
     *
     * @var \JsonSchema\Validator
     */
    protected $jsonValidator;

    /**
     * Constructor.
     *
     * @param \JsonSchema\Validator $jsonValidator
     */
    public function __construct(JsonValidator $jsonValidator)
    {
        $this->middleware('auth:api')->except([
            'authenticate',
            'resetPassword',
            'validateEmail',
            'confirmEmailVerification',
        ]);

        $this->jsonValidator = $jsonValidator;
    }

    /**
     * Get users.
     *
     * @param \Illuminate\http\Request $request
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Bmatovu\QueryDecorator\Exceptions\InvalidJsonException
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', [User::class]);

        // Validate request query parameters.

        $schemaPath = resource_path('js/schemas/users.json');

        Schema::validate($this->jsonValidator, $schemaPath, $request->query());

        // Query users.

        $query = User::query();

        $consumer = Auth::guard('api')->user();

        $query->onlyRelated($consumer)->withTrashed();

        // Apply constraints to query.

        $tableModelMap = [
            'users' => null,
            'roles' => 'role',
            'facilities' => 'facility',
        ];

        $constraints = (array) $request->query('filters');

        $query = Decorator::decorate($query, $constraints, $tableModelMap, true);

        // Pagination.

        $limit = $request->input('limit', 10);

        if ($request->input('paginate', true)) {
            return response($query->paginate($limit));
        }

        $users = $query->take($limit)->get();

        return response(['users' => $users]);
    }

    /**
     * Get users for jQuery datatables.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function datatables(Request $request)
    {
        $this->authorize('viewAny', [User::class]);

        // ...

        $params = (array) $request->query();

        $query = User::query();

        $consumer = Auth::guard('api')->user();

        $query->onlyRelated($consumer)->withTrashed();

        // ...

        $constraints = Datatable::buildConstraints($params, 'ilike');

        // ...

        $schemaPath = resource_path('js/schemas/users.json');

        Schema::validate($this->jsonValidator, $schemaPath, $constraints);

        // ...

        $relations = Decorator::getRelations($constraints['select']);

        $isRelated = (bool) count($relations);

        if (in_array('role', $relations)) {
            $query->leftJoin('roles', 'roles.id', '=', 'users.role_id');
        }

        if (in_array('facility', $relations)) {
            $query->leftJoin('facilities', 'facilities.id', '=', 'users.facility_id');
        }

        $availableRecords = $query->count();

        // ...

        $tableModelMap = [
            'users' => null,
            'roles' => 'role',
            'facilities' => 'facility',
        ];

        $query = Decorator::decorate($query, $constraints, $tableModelMap, $isRelated);

        $matchedRecords = $query->get();

        $data = Decorator::resultsByModel($matchedRecords, $tableModelMap);

        return response([
            'draw' => (int) $constraints['draw'],
            'recordsTotal' => $availableRecords,
            'recordsFiltered' => isset($constraints['filter']) ? $matchedRecords->count() : $availableRecords,
            'data' => $data,
        ]);
    }

    /**
     * Get specific user.
     *
     * @param string $userId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function show($userId)
    {
        $this->authorize('view', [User::class, $userId]);

        $consumer = Auth::guard('api')->user();

        $user = User::onlyRelated($consumer)
            ->with(['role', 'facility'])
            ->withTrashed()
            ->findOrFail($userId);

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

        $registrar = Auth::guard('api')->user();

        $role = Role::onlyRelated($registrar)->find($request->role_id);

        if (! $role) {
            $validator = Validator::make([], []);
            $validator->errors()->add('role_id', 'Unknown role.');

            throw new ValidationException($validator);
        }

        $user = new User();
        $user->name = $request->name;
        $user->alias = $request->alias;
        $user->email = $request->email;
        $user->password = Hash::make(Str::random(10));
        $user->facility()->associate($registrar->facility);
        $user->role()->associate($role);
        $user->save();

        $user->refresh();

        return response($user, 201);
    }

    /**
     * Update specific user.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $userId
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $userId)
    {
        $this->authorize('update', [User::class, $userId]);

        $registrar = Auth::guard('api')->user();

        $user = User::with(['role', 'facility'])->onlyRelated($registrar)->findOrFail($userId);

        $this->validate($request, [
            'name' => 'sometimes|max:25',
            'alias' => "sometimes|unique:users,alias,{$userId},id",
            'email' => "sometimes|unique:users,email,{$userId},id",
            'role_id' => 'nullable|uuid',
        ]);

        if ($request->filled('role_id')) {
            $role = Role::onlyRelated($registrar)->find($request->role_id);

            if (! $role) {
                $validator = Validator::make([], []);
                $validator->errors()->add('role_id', 'Unknown role.');

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
     * @param string $userId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function revoke($userId)
    {
        $this->authorize('softDelete', [User::class, $userId]);

        $user = Auth::guard('api')->user();

        $user = User::with(['role', 'facility'])->onlyRelated($user)->findOrFail($userId);

        $user->delete();

        $user->refresh();

        return response($user);
    }

    /**
     * Restore the specific banned user.
     *
     * @param string $userId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function restore($userId)
    {
        $this->authorize('restore', [User::class, $userId]);

        $user = Auth::guard('api')->user();

        $user = User::with(['role', 'facility'])->onlyRelated($user)->onlyTrashed()->findOrFail($userId);

        $user->restore();

        $user->refresh();

        return response($user);
    }

    /**
     * Permanently delete the specific user.
     *
     * @param string $userId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($userId)
    {
        $this->authorize('forceDelete', [User::class, $userId]);

        $user = Auth::guard('api')->user();

        $user = User::onlyRelated($user)->onlyTrashed()->findOrFail($userId);

        $user->forceDelete();

        return response(null, 204);
    }

    /**
     * Confirm your account password.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return \Illuminate\Http\Response
     */
    public function confirmPassword(Request $request)
    {
        $user = Auth::guard('api')->user();

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
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::guard('api')->user();

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
     * Determine if a user exists with the given email address.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return \Illuminate\Http\Response
     */
    public function validateEmail(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|exists:users,email',
        ]);

        return response(null, 204);
    }

    /**
     * Confirm a user has verified their email address.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return \Illuminate\Http\Response
     */
    public function confirmEmailVerification(Request $request)
    {
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
     * Reset a user's forgotten password.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return \Illuminate\Http\Response
     */
    public function resetPassword(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'email_verified_at' => 'sometimes|date_format:Y-m-d H:i:s',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            $validator = Validator::make([], []);
            $validator->errors()->add('email', 'Wrong email address.');

            throw new ValidationException($validator);
        }

        $user->email_verified_at = $request->input('email_verified_at', $user->email_verified_at);
        $user->password = Hash::make($request->password);
        $user->save();

        return response(null, 204);
    }

    /**
     * Reset a user's forgotten password.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return \Illuminate\Http\Response
     */
    public function authenticate(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::with(['facility', 'role'])->where('email', $request->email)->first();

        if (! $user || ! password_verify($request->password, $user->password)) {
            $validator = Validator::make([], []);
            $validator->errors()->add('email', 'Wrong email or password.');

            throw new ValidationException($validator);
        }

        // ...

        $bearerToken = $request->bearerToken();

        $tokenId = (new \Lcobucci\JWT\Parser())->parse($bearerToken)->getHeader('jti');

        $token = \App\Models\Token::find($tokenId);

        $client = $token->client;

        if (! $client->password_client) {
            return response($user);
        }

        $headers = [
            'Accept' => 'application/json',
        ];

        $parameters = [
            'grant_type' => 'password',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'username' => $request->email,
            'password' => $request->password,
            'scope' => $request->input('scopes', $token->scopes),
        ];

        $user = $user->toArray();

        $user['token'] = $this->getToken('POST', 'oauth/token', $parameters, $headers);

        return response($user);
    }

    /**
     * Invalidate user tokens.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return \Illuminate\Http\Response
     */
    public function deauthenticate(Request $request)
    {
        $user = Auth::guard('api')->user();

        $token = $user->token();

        $token->revoke();

        // Revoke refresh token
        DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $token->id)
            ->update([
                'revoked' => true,
            ]);

        return response()->json(null, 204);
    }

    /**
     * Get authenticated client application.
     *
     * @link https://github.com/laravel/passport/issues/124#issuecomment-252434309
     * @link https://github.com/laravel/passport/issues/143#issuecomment-290443170
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Models\Client
     */
    protected function getClient(Request $request)
    {
        $bearerToken = $request->bearerToken();
        $tokenId = (new \Lcobucci\JWT\Parser())->parse($bearerToken)->getHeader('jti');

        return \Laravel\Passport\Token::find($tokenId)->client;
    }

    /**
     * Request for token.
     *
     * @param string $method
     * @param string $uri
     * @param array  $parameters
     * @param array  $headers
     *
     * @return null|array token
     */
    protected function getToken($method, $uri, $parameters = [], $headers = [])
    {
        // Symfony\Component\HttpFoundation\Request@create
        $request = Request::create($uri, $method, $parameters);
        $request->headers->add($headers);

        try {
            $response = app()->handle($request);

            return json_decode((string) $response->getContent(), true);
        } catch (\Exception $e) {
            Log::error(json_encode([
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ]));

            return null;
        }
    }
}
