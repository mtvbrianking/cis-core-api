<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Bmatovu\QueryDecorator\Json\Schema;
use Bmatovu\QueryDecorator\Query\Decorator;
use Bmatovu\QueryDecorator\Support\Datatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use JsonSchema\Validator as JsonValidator;

class PermissionController extends Controller
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
     * Get permissions.
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
        $this->authorize('viewAny', [Permission::class]);

        // Validate request query parameters.

        $schemaPath = resource_path('js/schemas/permissions.json');

        Schema::validate($this->jsonValidator, $schemaPath, $request->query());

        // Query permissions.

        $query = Permission::query();

        // Apply constraints to query.
        $query = Decorator::decorate($query, (array) $request->query('filters'));

        // Pagination.

        $limit = $request->input('limit', 10);

        if ($request->input('paginate', true)) {
            return response($query->paginate($limit));
        }

        $permissions = $query->take($limit)->get();

        return response(['permissions' => $permissions]);
    }

    /**
     * Get permissions for jQuery datatables.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Bmatovu\QueryDecorator\Exceptions\InvalidJsonException
     *
     * @return \Illuminate\Http\Response
     */
    public function datatables(Request $request)
    {
        $this->authorize('viewAny', [Permission::class]);

        // ...

        $params = (array) $request->query();

        // ...

        $constraints = Datatable::buildConstraints($params, 'ilike');

        $schemaPath = resource_path('js/schemas/permissions.json');

        Schema::validate($this->jsonValidator, $schemaPath, $constraints);

        // ...

        $query = Permission::query();

        $query = Decorator::decorate($query, $constraints);

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
     * Register new permission.
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
        $this->authorize('create', [Permission::class]);

        $validator = Validator::make($request->all(), [
            'name'          => 'required',
            'description'   => 'sometimes|max:25',
            'module_name'   => 'required|exists:modules,name',
        ]);

        $validator->after(function ($validator) use ($request) {
            $name = Str::slug($request->name);

            $permission = Permission::where('name', $name)->where('module_name', $request->module_name)->first();

            if ($permission) {
                $validator->errors()->add('name', 'The name has already been taken.');
            }
        });

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $permission = new Permission();

        $permission->module_name = $request['module_name'];
        $permission->name = $request['name'];
        $permission->description = $request['description'];

        $permission->save();

        $permission->refresh();

        return response($permission, 201);
    }

    /**
     * Get specific permission.
     *
     * @param int $permissionId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function show($permissionId)
    {
        $this->authorize('view', [Permission::class]);

        $permission = Permission::with('module')->findOrFail($permissionId);

        return response($permission);
    }

    /**
     * Update specific permission.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $permissionId
     *
     * @throws ValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $permissionId)
    {
        $this->authorize('update', [Permission::class]);

        $validator = Validator::make($request->all(), [
            'name'          => 'required',
            'module_name'   => 'required|exists:modules,name',
            'description'   => 'sometimes|max:25',
        ]);

        $validator->after(function ($validator) use ($request, $permissionId) {
            $name = Str::slug($request->name);

            $permission = Permission::where('name', $name)
                ->where('module_name', $request->module_name)
                ->where('id', '<>', $permissionId)
                ->first();

            if ($permission) {
                $validator->errors()->add('name', 'The name has already been taken.');
            }
        });

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $permission = Permission::findOrFail($permissionId);

        $permission->description = $request->description;
        $permission->name = $request->name;
        $permission->save();

        $permission->refresh();

        return response($permission);
    }

    /**
     * Permanently delete the specific permission.
     *
     * @param int $permissionId
     *
     * @throws \Exception
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($permissionId)
    {
        $this->authorize('delete', [Permission::class]);

        $permission = Permission::findOrFail($permissionId);

        $permission->delete();

        return response(null, 204);
    }
}
