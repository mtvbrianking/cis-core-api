<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use App\Rules\Tel;
use App\Support\Datatable;
use App\Traits\JqueryDatatables;
use App\Traits\JsonValidation;
use App\Traits\QueryDecoration;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use JsonSchema\Validator as JsonValidator;

class FacilityController extends Controller
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
     * Get all facilities.
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
        $this->authorize('viewAny', [Facility::class]);

        // Validate request query parameters.

        $schemaPath = resource_path('js/schemas/facilities.json');

        static::validateJson($this->jsonValidator, $schemaPath, $request->query());

        // Query facilities.

        $query = Facility::query();

        $query->withTrashed();

        // Apply constraints to query.

        $query = static::applyConstraintsToQuery($query, $request);

        // Pagination.

        $limit = $request->input('limit', 10);

        if ($request->input('paginate', true)) {
            return response($query->paginate($limit));
        }

        $facilities = $query->take($limit)->get();

        return response(['facilities' => $facilities]);
    }

    /**
     * Get facilities for jQuery datatables.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \App\Exceptions\InvalidJsonException
     *
     * @return \Illuminate\Http\Response
     */
    public function datatables(Request $request)
    {
        $this->authorize('viewAny', [Facility::class]);

        // ...

        $query = Facility::query();

        // ...

        $constraints = Datatable::prepareQueryParameters($request->query());

        // ...

        $schemaPath = resource_path('js/schemas/facilities.json');

        static::validateJson($this->jsonValidator, $schemaPath, $constraints);

        // ...

        $tableModelMap = [
            'facilities' => null,
        ];

        return static::queryForDatatables($query, $constraints, $tableModelMap);
    }

    /**
     * Store a newly created facility in storage.
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
        $this->authorize('create', [Facility::class]);

        $this->validate($request, [
            'name' => 'required|max:100',
            'description' => 'nullable|max:100',
            'address' => 'required|max:50',
            'email' => 'required|email|max:50',
            'website' => 'nullable|url|max:50',
            'phone' => ['nullable', new Tel, 'max:25'],
        ]);

        $facility = new Facility();
        $facility->name = $request->name;
        $facility->description = $request->description;
        $facility->address = $request->address;
        $facility->email = $request->email;
        $facility->website = $request->website;
        $facility->phone = $request->phone;
        $facility->save();

        $facility->refresh();

        return response($facility, 201);
    }

    /**
     * Get the specified facility.
     *
     * @param string $facilityId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function show($facilityId)
    {
        $this->authorize('view', [Facility::class, $facilityId]);

        $facility = Facility::withTrashed()->findOrFail($facilityId);

        return response($facility);
    }

    /**
     * Update the specified facility in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $facilityId
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $facilityId)
    {
        $this->authorize('update', [Facility::class]);

        $this->validate($request, [
            'name' => 'sometimes|max:100',
            'description' => 'nullable|max:100',
            'address' => 'sometimes|max:50',
            'email' => 'sometimes|email|max:50',
            'website' => 'nullable|url|max:50',
            'phone' => ['nullable', new Tel, 'max:25'],
        ]);

        $facility = Facility::findOrFail($facilityId);
        $facility->name = $request->input('name', $facility->name);
        $facility->description = $request->description;
        $facility->address = $request->input('address', $facility->address);
        $facility->email = $request->input('email', $facility->email);
        $facility->website = $request->website;
        $facility->phone = $request->phone;
        $facility->save();

        $facility->refresh();

        return response($facility);
    }

    /**
     * Temporarily delete (ban) the specific facility.
     *
     * @param string $facilityId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     *
     * @return \Illuminate\Http\Response
     */
    public function revoke($facilityId)
    {
        $this->authorize('softDelete', [Facility::class]);

        $facility = Facility::findOrFail($facilityId);

        $facility->delete();

        $facility->refresh();

        return response($facility);
    }

    /**
     * Restore the specific banned facility.
     *
     * @param string $facilityId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function restore($facilityId)
    {
        $this->authorize('restore', [Facility::class]);

        $facility = Facility::onlyTrashed()->findOrFail($facilityId);

        $facility->restore();

        $facility->refresh();

        return response($facility);
    }

    /**
     * Permanently delete the specific facility.
     *
     * @param string $facilityId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($facilityId)
    {
        $this->authorize('forceDelete', [Facility::class]);

        $facility = Facility::onlyTrashed()->findOrFail($facilityId);

        // ...

        $users = User::withTrashed()->where('facility_id', $facilityId)->count();

        $roles = Role::withTrashed()->where('facility_id', $facilityId)->count();

        $dependants = max($users, $roles);

        if ($dependants) {
            return response(['message' => "Can't delete non-orphaned facility."], 400);
        }

        // ...

        $facility->forceDelete();

        return response(null, 204);
    }

    /**
     * Roles belonging to this facility.
     *
     * @param string $facilityId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function roles($facilityId)
    {
        $this->authorize('viewAny', [Role::class]);

        $facility = Facility::with('roles')->findOrFail($facilityId);

        return response($facility);
    }

    /**
     * Users belonging to this facility.
     *
     * @param string $facilityId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function users($facilityId)
    {
        $this->authorize('viewAny', [User::class]);

        $facility = Facility::with('users')->findOrFail($facilityId);

        return response($facility);
    }

    /**
     * Modules granted to this facility.
     *
     * @param string $facilityId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function modules($facilityId)
    {
        $this->authorize('viewAny', [Module::class]);

        $facility = Facility::with('modules')->findOrFail($facilityId);

        return response($facility);
    }

    /**
     * Query modules available to a facility.
     *
     * Indicating whether or not a module is granted to that facility.
     *
     * @param \App\Models\Facility $facility
     *
     * @return \Illuminate\Support\Collection
     */
    protected function queryModulesAvailable(Facility $facility): Collection
    {
        $query = Module::query();

        $query->leftJoin('facility_module', function ($join) use ($facility) {
            $join->on('facility_module.module_name', '=', 'modules.name');
            $join->where('facility_module.facility_id', '=', $facility->id);
        });

        $query->select([
            'modules.name',
            'modules.category',
            'facility_module.facility_id',
        ]);

        $modules = $query->get();

        return $modules->map(function ($module) {
            return [
                'name' => $module->name,
                'category' => $module->category,
                'granted' => ! is_null($module->facility_id),
            ];
        });
    }

    /**
     * Only modules available to this facility.
     *
     * @param string $facilityId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function modulesAvailable($facilityId)
    {
        $this->authorize('assignModules', [Module::class]);

        $facility = Facility::findOrFail($facilityId);

        $facility->modules = $this->queryModulesAvailable($facility);

        return response($facility);
    }

    /**
     * Reassign modules granted to a facility.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $facilityId
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function syncModulesAvailable(Request $request, $facilityId)
    {
        $this->authorize('assignModules', [Module::class]);

        $facility = Facility::findOrFail($facilityId);

        $this->validate($request, [
            'modules' => 'required|array',
            'modules.*' => 'required|string',
        ]);

        $available_mods = Module::get()->map(function ($module) {
            return $module->name;
        })->toArray();

        $unknown_mods = array_values(array_diff($request->modules, $available_mods));

        if ($unknown_mods) {
            $validator = Validator::make([], []);
            $validator->errors()->add('modules', 'Unknown modules: '.implode(', ', $unknown_mods));

            throw new ValidationException($validator);
        }

        // Sync modules...
        $facility->modules()->sync($request->modules, true);
        $facility->save();

        $facility->modules = $this->queryModulesAvailable($facility);

        return response($facility);
    }
}
