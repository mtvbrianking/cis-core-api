<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use App\Rules\Tel;
use App\Traits\JsonValidation;
use App\Traits\QueryDecoration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use JsonSchema\Validator as JsonValidator;

class FacilityController extends Controller
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

        static::validateJson($this->jsonValidator, $schemaPath, $request);

        // Query facilities.

        $query = Facility::query();

        $query->withTrashed();

        // Apply constraints to query.

        $query = static::applyConstraintsToQuery($query, $request);

        // Pagination.

        $limit = $request->input('limit', 15);

        $facilities = $request->input('paginate', true)
            ? $query->paginate($limit)
            : $query->take($limit)->get();

        // $facilities->withPath(url()->full());

        return response(['facilities' => $facilities]);
    }

    /**
     * Get facilities for jQuery datatables.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Exception
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function indexDt(Request $request)
    {
        $this->authorize('viewAny', [Facility::class]);

        $query = Facility::query();

        $query->withTrashed();

        $availableFacilities = $query->count();

        // Datatables

        $allowedCols = [
            'facilities.id',
            'facilities.name',
            'facilities.description',
            'facilities.address',
            'facilities.email',
            'facilities.website',
            'facilities.phone',
            'facilities.created_at',
            'facilities.updated_at',
            'facilities.deleted_at',
        ];

        $params = $request->query();

        $cols = array_get($params, 'columns', []);

        $askedCols = array_map(function ($col) {
            return $col['data'];
        }, $cols);

        $validCols = array_intersect($askedCols, $allowedCols);

        // Select

        $query->select($validCols);

        // Filter

        foreach ($cols as $col) {
            if (! in_array($col['data'], $validCols)) {
                continue;
            }

            if (! $col['searchable']) {
                continue;
            }

            // Column filter

            if ($term = $col['search']['value']) {
                $query->orWhere($col['data'], 'ilike', "%{$term}%");

                continue;
            }

            // Global filter

            if ($term = $params['search']['value']) {
                $query->orWhere($col['data'], 'ilike', "%{$term}%");
            }
        }

        // Order

        foreach ($params['order'] as $order) {
            $colIdx = $order['column'];

            $col = $cols[$colIdx];

            if (! in_array($col['data'], $validCols)) {
                continue;
            }

            if (! $col['orderable']) {
                continue;
            }

            $query->orderBy($col['data'], $order['dir']);
        }

        // Paginate

        $query->skip($params['start']);

        // Limit

        $query->take($params['length']);

        // $query->dump();

        $facilities = $query->get();

        return response([
            'draw' => ++$params['draw'],
            'recordsTotal' => $availableFacilities,
            'recordsFiltered' => $facilities->count(),
            'data' => $facilities,
        ]);
    }

    /**
     * Store a newly created facility in storage.
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
        $this->authorize('create', [Facility::class]);

        $this->validate($request, [
            'name' => 'required|max:100',
            'description' => 'nullable|max:100',
            'address' => 'required|max:50',
            'email' => 'required|email|max:50',
            'website' => 'nullable|url|max:50',
            'phone' => ['nullable', new Tel, 'max:25'],
        ]);

        $user = Auth::guard('api')->user();

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
     * @param string $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->authorize('view', [Facility::class, $id]);

        $facility = Facility::withTrashed()->findOrFail($id);

        return response($facility);
    }

    /**
     * Update the specified facility in storage.
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
        $this->authorize('update', [Facility::class]);

        $this->validate($request, [
            'name' => 'sometimes|max:100',
            'description' => 'nullable|max:100',
            'address' => 'sometimes|max:50',
            'email' => 'sometimes|email|max:50',
            'website' => 'nullable|url|max:50',
            'phone' => ['nullable', new Tel, 'max:25'],
        ]);

        $user = Auth::guard('api')->user();

        $facility = Facility::findOrFail($id);
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
     * @param string $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     *
     * @return \Illuminate\Http\Response
     */
    public function revoke($id)
    {
        $this->authorize('soft-delete', [Facility::class]);

        $facility = Facility::findOrFail($id);

        $facility->delete();

        $facility->refresh();

        return response($facility);
    }

    /**
     * Restore the specific banned facility.
     *
     * @param string $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        $this->authorize('restore', [Facility::class]);

        $facility = Facility::onlyTrashed()->findOrFail($id);

        $facility->restore();

        $facility->refresh();

        return response($facility);
    }

    /**
     * Permanently delete the specific facility.
     *
     * @param string $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->authorize('force-delete', [Facility::class]);

        $facility = Facility::onlyTrashed()->findOrFail($id);

        // ...

        $users = User::withTrashed()->where('facility_id', $id)->count();

        $roles = Role::withTrashed()->where('facility_id', $id)->count();

        $dependants = max($users, $roles);

        if ($dependants) {
            return response(['message' => "Can't delete non-orphaned facility."], 400);
        }

        // ...

        $facility->forceDelete();

        return response(null, 204);
    }

    /**
     * Update facility module access.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $id
     *
     * @throws ValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function syncModules(Request $request, $id)
    {
        $this->authorize('assign-modules', [Module::class]);

        $facility = Facility::findOrFail($id);

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

        $facility = Facility::with('modules')->find($id);

        return response($facility);
    }
}
