<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Module;
use App\Models\Permission;
use App\Support\Datatable;
use App\Traits\JqueryDatatables;
use App\Traits\JsonValidation;
use App\Traits\QueryDecoration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use JsonSchema\Validator as JsonValidator;

class ModuleController extends Controller
{
    use JsonValidation, JqueryDatatables, QueryDecoration;

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
     * Get modules.
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
        $this->authorize('viewAny', [Module::class]);

        // Validate request query parameters.

        $schemaPath = resource_path('js/schemas/modules.json');

        static::validateJson($this->jsonValidator, $schemaPath, $request->query());

        // Query modules.

        $query = Module::query();

        $query->withTrashed();

        // Apply constraints to query.

        $query = static::applyConstraintsToQuery($query, $request);

        // Pagination.

        $limit = $request->input('limit', 15);

        $modules = $request->input('paginate', true)
            ? $query->paginate($limit)
            : $query->take($limit)->get();

        // $modules->withPath(url()->full());

        return response(['modules' => $modules]);
    }

    /**
     * Get modules for jQuery datatables.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function indexDt(Request $request)
    {
        $this->authorize('viewAny', [Module::class]);

        // ...

        $query = Module::query();

        // ...

        $constraints = Datatable::prepareQueryParameters($request->query());

        // return response($constraints);

        // ...

        $schemaPath = resource_path('js/schemas/modules.json');

        static::validateJson($this->jsonValidator, $schemaPath, $constraints);

        // ...

        $tableModelMap = [
            'modules' => null,
        ];

        return static::queryForDatatables($query, $constraints, $tableModelMap);
    }

    /**
     * Register new module.
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
        $this->authorize('create', [Module::class]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:20',
            'description' => 'sometimes|max:25',
        ]);

        $validator->after(function ($validator) use ($request) {
            $name = Str::slug(Str::plural($request->name));

            $module = Module::where('name', $name)->first();

            if ($module) {
                $validator->errors()->add('name', 'The name has already been taken.');
            }
        });

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $module = new Module();
        $module->name = $request->name;
        $module->description = $request->description;
        $module->save();

        $module->refresh();

        return response($module, 201);
    }

    /**
     * Get specific module.
     *
     * @param string $moduleName
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function show($moduleName)
    {
        $this->authorize('view', [Module::class]);

        $module = Module::withTrashed()->findOrFail($moduleName);

        return response($module);
    }

    /**
     * Get facilities with this module.
     *
     * @param string $moduleName
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function facilities($moduleName)
    {
        $this->authorize('viewAny', [Facility::class]);

        $module = Module::with('facilities')->findOrFail($moduleName);

        return response($module);
    }

    /**
     * Get permissions beloging to this module.
     *
     * @param string $moduleName
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function permissions($moduleName)
    {
        $this->authorize('viewAny', [Permission::class]);

        $module = Module::with('permissions')->findOrFail($moduleName);

        return response($module);
    }

    /**
     * Update specific module.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $moduleName
     *
     * @throws ValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $moduleName)
    {
        $this->authorize('update', [Module::class]);

        $module = Module::findOrFail($moduleName);

        $this->validate($request, [
            'description' => 'nullable|max:25',
        ]);

        $module->description = $request->description;
        $module->save();

        $module->refresh();

        return response($module);
    }

    /**
     * Temporarily delete (ban) the specific module.
     *
     * @param string $moduleName
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     *
     * @return \Illuminate\Http\Response
     */
    public function revoke($moduleName)
    {
        $this->authorize('soft-delete', [Module::class]);

        $module = Module::findOrFail($moduleName);

        $module->delete();

        $module->refresh();

        return response($module);
    }

    /**
     * Restore the specific banned module.
     *
     * @param string $moduleName
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function restore($moduleName)
    {
        $this->authorize('restore', [Module::class]);

        $module = Module::onlyTrashed()->findOrFail($moduleName);

        $module->restore();

        $module->refresh();

        return response($module);
    }

    /**
     * Permanently delete the specific module.
     *
     * @param string $moduleName
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($moduleName)
    {
        $this->authorize('force-delete', [Module::class]);

        $module = Module::onlyTrashed()->findOrFail($moduleName);

        // ...

        $dependants = Permission::where('module_name', $moduleName)->count();

        if ($dependants) {
            return response(['message' => "Can't delete non-orphaned module."], 400);
        }

        // ...

        $module->forceDelete();

        return response(null, 204);
    }
}
