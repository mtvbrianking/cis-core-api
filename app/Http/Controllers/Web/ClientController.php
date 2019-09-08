<?php

namespace App\Http\Controllers\Web;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Laravel\Passport\Passport;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
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
     * Display a listing of the resource.
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
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
     * Show the form for editing the specified resource.
     *
     * @param string $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $client = Passport::client()->findOrFail($id);

        return view('clients.edit', ['client' => $client]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $id
     *
     * @return \Illuminate\Http\Response
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

        if(!$request->password_client && !$request->personal_access_client && !$request->redirect) {
            $validator->getMessageBag()->add('redirect', 'Redirect URI is required for Authorization Code Client.');

            return redirect()->back()->withInput()->withErrors($validator);
        }

        $client = Passport::client()->find($id);

        if($request->regenerate_secret) {
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
     * Remove the specified resource from storage.
     *
     * @param string $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
