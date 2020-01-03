<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

use Bmatovu\QueryDecorator\Json\Schema;
use Bmatovu\QueryDecorator\Query\Decorator;
use Bmatovu\QueryDecorator\Support\Datatable;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use JsonSchema\Validator as JsonValidator;

class RoleController extends Controller
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
        $this->middleware('auth:api');

        $this->jsonValidator = $jsonValidator;
    }

    /**
     * Get roles.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Bmatovu\QueryDecorator\Exceptions\InvalidJsonException
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', [Role::class]);

        // Validate request query parameters.

        $schemaPath = resource_path('js/schemas/roles.json');

        Schema::validate($this->jsonValidator, $schemaPath, $request->query());

        // Query roles.

        $query = Role::query();

        $user = Auth::guard('api')->user();

        $query->onlyRelated($user);

        $query->withTrashed();

        // Apply constraints to query.

        $query = Decorator::decorate($query, (array) $request->query('filters'));

        // Pagination.

        $limit = $request->input('limit', 10);

        if ($request->input('paginate', true)) {
            return response($query->paginate($limit));
        }

        $roles = $query->take($limit)->get();

        return response(['roles' => $roles]);
    }

    /**
     * Get roles for jQuery datatables.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function datatables(Request $request)
    {
        $this->authorize('viewAny', [Role::class]);

        // ...

        $params = (array) $request->query();

        // ...

        $constraints = Datatable::buildConstraints($params, 'ilike');

        // ...

        $schemaPath = resource_path('js/schemas/roles.json');

        Schema::validate($this->jsonValidator, $schemaPath, $constraints);

        // ...

        $query = Role::query();

        $availableRecords = $query->count();

        $query = Decorator::decorate($query, $constraints);

        $matchedRecords = $query->get();

        return response([
            'draw' => (int) $constraints['draw'],
            'recordsTotal' => $availableRecords,
            'recordsFiltered' => isset($constraints['filter']) ? $matchedRecords->count() : $availableRecords,
            'data' => $matchedRecords,
        ]);
    }

    /**
     * Register new role.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws ValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', [Role::class]);

        $this->validate($request, [
            'name' => 'required|max:25',
            'description' => 'sometimes|max:50',
        ]);

        $user = Auth::guard('api')->user();

        $role = new Role();
        $role->name = $request->name;
        $role->description = $request->description;
        $role->facility()->associate($user->facility);
        $role->save();

        $role->refresh();

        return response($role, 201);
    }

    /**
     * Get specific role.
     *
     * @param string $roleId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function show($roleId)
    {
        $this->authorize('view', [Role::class, $roleId]);

        $user = Auth::guard('api')->user();

        $role = Role::with('facility')
            ->onlyRelated($user)
            ->withTrashed()
            ->findOrFail($roleId);

        return response($role);
    }

    /**
     * Update specific role.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $roleId
     *
     * @throws ValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $roleId)
    {
        $this->authorize('update', [Role::class]);

        $user = Auth::guard('api')->user();

        $role = Role::onlyRelated($user)->findOrFail($roleId);

        $this->validate($request, [
            'name' => 'required|max:25',
            'description' => 'sometimes|max:50',
        ]);

        $role->name = $request->name;
        $role->description = $request->description;
        $role->save();

        $role->refresh();

        return response($role);
    }

    /**
     * Temporarily delete (ban) the specific role.
     *
     * @param string $roleId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function revoke($roleId)
    {
        $this->authorize('softDelete', [Role::class]);

        $user = Auth::guard('api')->user();

        $role = Role::onlyRelated($user)->findOrFail($roleId);

        $role->delete();

        $role->refresh();

        return response($role);
    }

    /**
     * Restore the specific banned role.
     *
     * @param string $roleId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function restore($roleId)
    {
        $this->authorize('restore', [Role::class]);

        $user = Auth::guard('api')->user();

        $role = Role::onlyRelated($user)->onlyTrashed()->findOrFail($roleId);

        $role->restore();

        $role->refresh();

        return response($role);
    }

    /**
     * Permanently delete the specific role.
     *
     * @param string $roleId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($roleId)
    {
        $this->authorize('forceDelete', [Role::class]);

        $user = Auth::guard('api')->user();

        $role = Role::onlyRelated($user)->onlyTrashed()->findOrFail($roleId);

        // ...

        $dependants = User::withTrashed()->where('role_id', $roleId)->count();

        if ($dependants) {
            return response(['message' => "Can't delete non-orphaned role."], 400);
        }

        // ...

        $role->forceDelete();

        return response(null, 204);
    }

    /**
     * Get users assigned this role.
     *
     * @param string $roleId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function users($roleId)
    {
        $this->authorize('viewAny', [User::class]);

        $user = Auth::guard('api')->user();

        $role = Role::onlyRelated($user)->with('users')->findOrFail($roleId);

        return response($role);
    }

    /**
     * Permissions assigned to this role.
     *
     * @param string $roleId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function permissions($roleId)
    {
        $this->authorize('viewAny', [Permission::class]);

        $user = Auth::guard('api')->user();

        $role = Role::onlyRelated($user)->with('permissions')->findOrFail($roleId);

        return response($role);
    }

    /**
     * Permissions available to this role.
     *
     * @param string $roleId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function permissionsAvailable($roleId)
    {
        $this->authorize('assignPermissions', [Permission::class]);

        $user = Auth::guard('api')->user();

        $role = Role::onlyRelated($user)->findOrFail($roleId);

        // ...

        $query = Permission::query();

        $query->join('modules', 'permissions.module_name', '=', 'modules.name');

        $query->leftJoin('role_permission', function ($join) use ($roleId) {
            $join->on('permissions.id', '=', 'role_permission.permission_id');
            $join->where('role_permission.role_id', '=', $roleId);
        });

        $query->select([
            'permissions.id',
            'permissions.name',
            'modules.category AS module_category',
            'permissions.module_name',
            'role_permission.role_id',
        ]);

        $query->whereIn('module_name', function ($query) use ($role) {
            $query->from('facility_module')
                ->select('module_name')
                ->where('facility_id', $role->facility_id);
        });

        $permissions = $query->get();

        // ...

        $permissions = $permissions->map(function ($permission) {
            return [
                'id' => $permission->id,
                'name' => $permission->name,
                'module' => [
                    'category' => $permission->module_category,
                    'name' => $permission->module_name,
                ],
                'granted' => !is_null($permission->role_id),
            ];
        });

        $role->permissions = $permissions;

        return response($role);
    }

    /**
     * Sync role permissions.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $roleId
     *
     * @throws ValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function syncPermissionsAvailable(Request $request, $roleId)
    {
        $this->authorize('assignPermissions', [Permission::class]);

        $role = Role::findOrFail($roleId);

        $this->validate($request, [
            'permissions' => 'required|array',
            'permissions.*' => 'required|integer',
        ]);

        $user = Auth::guard('api')->user();

        $permissions = Permission::query()
            ->whereHas('module.facilities', function ($query) use ($user) {
                $query->where('facility_id', $user->facility_id);
            })
            ->get();

        $available_perms = $permissions->map(function ($permission) {
            return $permission->id;
        })->toArray();

        $unknown_perms = array_values(array_diff($request->permissions, $available_perms));

        if ($unknown_perms) {
            $validator = Validator::make([], []);
            $validator->errors()->add('permissions', 'Unknown permissions: ' . implode(', ', $unknown_perms));

            throw new ValidationException($validator);
        }

        // Sync permissions...
        $role->permissions()->sync($request->permissions, true);
        $role->save();

        $role = Role::with('permissions')->find($roleId);

        return response($role);
    }
}
