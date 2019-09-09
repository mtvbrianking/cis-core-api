<?php

namespace App\Http\Controllers\Web;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Laravel\Passport\Passport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
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
        $clients = Passport::client()
            // ->where('user_id', $userId)
            ->get();

        return view('clients.index', ['clients' => $clients]);
    }

    /**
     * Show the form for creating a new client.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('clients.create');
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
            'personal_access_client' => 'sometimes|bool',
            'password_client' => 'sometimes|bool',
            'redirect' => 'nullable|url',
            'regenerate_secret' => 'sometimes',
        ]);

        $validator->validate();

        if (! $request->password_client && ! $request->personal_access_client && ! $request->redirect) {
            $validator->getMessageBag()->add('redirect', 'Redirect URI is required for Authorization Code Client.');

            return redirect()->back()->withInput()->withErrors($validator);
        }

        $client = Passport::client()->forceFill([
            'user_id' => Auth::user()->id,
            'name' => $request->name,
            'secret' => Str::random(40),
            'redirect' => (string) $request->redirect,
            'personal_access_client' => (bool) $request->personal_access_client,
            'password_client' => (bool) $request->password_client,
            'revoked' => false,
        ]);

        $client->save();

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
        $client = Passport::client()->findOrFail($id);

        return view('clients.show', ['client' => $client]);
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
        $client = Passport::client()->findOrFail($id);

        return view('clients.edit', ['client' => $client]);
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
            'personal_access_client' => 'sometimes|bool',
            'password_client' => 'bool|required_without:personal_access_client',
            'redirect' => 'nullable|url',
            'regenerate_secret' => 'sometimes',
        ]);

        $validator->validate();

        if (! $request->password_client && ! $request->personal_access_client && ! $request->redirect) {
            $validator->getMessageBag()->add('redirect', 'Redirect URI is required for Authorization Code Client.');

            return redirect()->back()->withInput()->withErrors($validator);
        }

        $client = Passport::client()->find($id);

        if ($request->regenerate_secret) {
            $client->secret = Str::random(40);
        }

        $client->name = Str::title($request->name);
        $client->personal_access_client = (bool) $request->personal_access_client;
        $client->password_client = (bool) $request->password_client;
        $client->redirect = (string) $request->redirect;
        $client->save();

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
        $client = Passport::client()->find($id);
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
        $client = Passport::client()->find($id);
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
        $client = Passport::client()->find($id);
        $client->delete();

        return response()->json(null, 204);
    }
}
