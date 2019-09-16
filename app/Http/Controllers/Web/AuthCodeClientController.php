<?php

namespace App\Http\Controllers\Web;

use App\Models\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthCodeClientController extends Controller
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
        return view('clients');

        $query = Client::query();
        $query->where('user_id', Auth::user()->id);
        $query->where('personal_access_client', false);
        $clients = $query->get();

        return view('auth-code-clients.index', ['clients' => $clients]);
    }

    /**
     * Show the form for creating a new client.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth-code-clients.create');
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
            'redirect' => 'required|url',
        ]);

        $validator->validate();

        $client = new Client();
        $client->forceFill([
            'user_id' => Auth::user()->id,
            'name' => $request->name,
            'secret' => Str::random(40),
            'redirect' => $request->redirect,
            'personal_access_client' => false,
            'password_client' => false,
            'revoked' => false,
        ]);
        $client->save();

        flash("Registered {$request->name}.")->success();

        return redirect()->route('clients.show', $client->id);
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
        $query = Client::query();
        $query->where('user_id', Auth::user()->id);
        $query->where('personal_access_client', false);
        $client = $query->findOrFail($id);

        return view('auth-code-clients.show', ['client' => $client]);
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
        $query = Client::query();
        $query->where('user_id', Auth::user()->id);
        $query->where('personal_access_client', false);
        $client = $query->findOrFail($id);

        return view('auth-code-clients.edit', ['client' => $client]);
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
            'redirect' => 'required|url',
            'regenerate_secret' => 'sometimes',
        ]);

        $validator->validate();

        $query = Client::query();
        $query->where('user_id', Auth::user()->id);
        $query->where('personal_access_client', false);
        $client = $query->findOrFail($id);

        if ($request->regenerate_secret) {
            $client->secret = Str::random(40);
        }

        $client->name = $request->name;
        $client->personal_access_client = false;
        $client->password_client = false;
        $client->redirect = (string) $request->redirect;
        $client->save();

        flash("Updated {$request->name}.")->success();

        return redirect()->route('clients.show', $id);
    }

    /**
     * Revoke the specified client.
     *
     * @param string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function revoke($id)
    {
        $query = Client::query();
        $query->where('user_id', Auth::user()->id);
        $query->where('personal_access_client', false);
        $client = $query->findOrFail($id);

        $client->revoked = true;
        $client->save();

        return response()->json($client);
    }

    /**
     * Restore the specified client.
     *
     * @param string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id)
    {
        $query = Client::query();
        $query->where('user_id', Auth::user()->id);
        $query->where('personal_access_client', false);
        $client = $query->findOrFail($id);

        $client->revoked = false;
        $client->save();

        return response()->json($client);
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
        $query = Client::query();
        $query->where('user_id', Auth::user()->id);
        $query->where('personal_access_client', false);
        $client = $query->findOrFail($id);

        $client->delete();

        return response()->json(null, 204);
    }
}
