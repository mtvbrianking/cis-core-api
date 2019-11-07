<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Traits\JsonValidation;
use App\Traits\QueryDecoration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use JsonSchema\Validator as JsonValidator;

class PermissionController extends Controller
{
    use JsonValidation, QueryDecoration;

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
     * @throws \App\Exceptions\InvalidJsonException
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', [Permission::class]);

        // Validate request query parameters.

        $schemaPath = resource_path('js/schemas/permissions.json');

        static::validateJson($this->jsonValidator, $schemaPath, $request);

        // Query permissions.

        $query = Permission::query();

        // Apply constraints to query.

        $query = static::applyConstraintsToQuery($query, $request);

        // Pagination.

        $limit = $request->input('limit', 15);

        $permissions = $request->input('paginate', true)
            ? $query->paginate($limit)
            : $query->take($limit)->get();

        // $permissions->withPath(url()->full());

        return response(['permissions' => $permissions]);
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
     * @param string $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->authorize('view', [Permission::class]);

        $permission = Permission::findOrFail($id);

        return response($permission);
    }

    /**
     * Update specific permission.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $id
     *
     * @throws ValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->authorize('update', [Permission::class]);

        $validator = Validator::make($request->all(), [
            'name'          => 'required',
            'module_name'   => 'required|exists:modules,name',
            'description'   => 'sometimes|max:25',
        ]);

        $validator->after(function ($validator) use ($request, $id) {
            $name = Str::slug($request->name);

            $permission = Permission::where('name', $name)
                ->where('module_name', $request->module_name)
                ->where('id', '<>', $id)
                ->first();

            if ($permission) {
                $validator->errors()->add('name', 'The name has already been taken.');
            }
        });

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $permission = Permission::findOrFail($id);

        $permission->description = $request->description;
        $permission->name = $request->name;
        $permission->save();

        $permission->refresh();

        return response($permission);
    }

    /**
     * Permanently delete the specific permission.
     *
     * @param string $id
     *
     * @throws \Exception
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->authorize('delete', [Permission::class]);

        $permission = Permission::findOrFail($id);

        $permission->delete();

        return response(null, 204);
    }
}
