<?php

namespace App\Http\Controllers\Web;

use App\Models\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PersonalAccessClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Personal Access Clients.
 */
class PersonalAccessClientController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the clients.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $query = PersonalAccessClient::query();
        $query->with('client');
        $query->whereHas('client', function ($query) {
            $query->where('user_id', Auth::user()->id);
        });
        $clients = $query->get();

        return view('personal-clients.index', ['clients' => $clients]);
    }

    /**
     * Show the form for creating a new client.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('personal-clients.create');
    }

    /**
     * Store a newly created client in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:50',
        ]);

        $validator->validate();

        $client = new Client();
        $client->forceFill([
            'user_id' => Auth::user()->id,
            'name' => $request->name,
            'secret' => Str::random(40),
            'redirect' => '',
            'personal_access_client' => true,
            'password_client' => false,
            'revoked' => false,
        ]);
        $client->save();

        $personal_client = new PersonalAccessClient();
        // $personal_client->forceFill([
        //     'client_id' => $client->id,
        // ]);
        $personal_client->client()->associate($client);
        $personal_client->save();

        flash("Registered {$request->name}.")->success();

        return redirect()->route('clients.personal.show', $personal_client->id);
    }

    /**
     * Display the specified client.
     *
     * @param string $id
     *
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $query = PersonalAccessClient::query();
        $query->with('client');
        $query->whereHas('client', function ($query) {
            $query->where('user_id', Auth::user()->id);
        });
        $client = $query->findOrFail($id);

        return view('personal-clients.show', ['client' => $client]);
    }

    /**
     * Show the form for editing the specified client.
     *
     * @param string $id
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $query = PersonalAccessClient::query();
        $query->with('client');
        $query->whereHas('client', function ($query) {
            $query->where('user_id', Auth::user()->id);
        });
        $client = $query->findOrFail($id);

        return view('personal-clients.edit', ['client' => $client]);
    }

    /**
     * Update the specified client in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:50',
        ]);

        $validator->validate();

        $query = PersonalAccessClient::query();
        $query->with('client');
        $query->whereHas('client', function ($query) {
            $query->where('user_id', Auth::user()->id);
        });
        $personal_client = $query->findOrFail($id);

        $personal_client->client->name = $request->name;
        $personal_client->client->save();

        flash("Updated {$request->name}.")->success();

        return redirect()->route('clients.personal.show', $id);
    }

    /**
     * Remove the specified client from storage.
     *
     * @param string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $query = PersonalAccessClient::query();
        $query->with('client');
        $query->whereHas('client', function ($query) {
            $query->where('user_id', Auth::user()->id);
        });
        $personal_client = $query->findOrFail($id);

        $personal_client->client->delete();

        $personal_client->delete();

        return response()->json(null, 204);
    }
}
