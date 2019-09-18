<?php

namespace App\Http\Controllers\Web;

use App\Models\Token;
use App\Models\Client;
use Lcobucci\JWT\Parser;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Zend\Diactoros\Response;
use Laravel\Passport\Passport;
use Zend\Diactoros\ServerRequest;
use App\Http\Controllers\Controller;
use App\Models\PersonalAccessClient;
use Illuminate\Support\Facades\Auth;
use Lcobucci\JWT\Parser as JwtParser;
use Illuminate\Support\Facades\Validator;
use League\OAuth2\Server\AuthorizationServer;

/**
 * Personal Access Clients.
 */
class PersonalAccessClientController extends Controller
{
    /**
     * The authorization server instance.
     *
     * @var \League\OAuth2\Server\AuthorizationServer
     */
    protected $server;

    /**
     * The JWT token parser instance.
     *
     * @var \Lcobucci\JWT\Parser
     */
    protected $jwt;

    /**
     * Create a new controller instance.
     *
     * @param \League\OAuth2\Server\AuthorizationServer $server
     * @param \Lcobucci\JWT\Parser                      $jwt
     */
    public function __construct(AuthorizationServer $server, JwtParser $jwt)
    {
        $this->middleware('auth');
        $this->server = $server;
        $this->jwt = $jwt;
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

    /**
     * Generate token.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function token(Request $request, $id)
    {
        Validator::make($request->all(), [
            'scopes' => 'array|in:'.implode(',', Passport::scopeIds()),
        ])->validate();

        $user = Auth::user();

        $query = PersonalAccessClient::query();
        $query->with('client');
        $query->whereHas('client', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        });
        $personal_client = $query->findOrFail($id);

        $client = $personal_client->client;

        $serverRequest = $this->createServerRequest($client, $user->id, (array) $request->scopes);

        $serverResponse = $this->dispatchRequestToAuthorizationServer($serverRequest);

        $tokenId = $this->getTokenId($serverResponse['access_token']);

        $token = Token::where('id', $tokenId)->first();

        $token->forceFill([
            'user_id' => $user->id,
            'name' => $client->name,
        ]);
        $token->save();

        Token::where('client_id', $client->id)
            ->where('id', '<>', $tokenId)
            ->update(['revoked' => true]);

        return response()->json($serverResponse);
    }

    /**
     * Create a request instance for the given client.
     *
     * @param \Laravel\Passport\Client $client
     * @param mixed                    $userId
     * @param array                    $scopes
     *
     * @return \Zend\Diactoros\ServerRequest
     */
    protected function createServerRequest($client, $userId, array $scopes)
    {
        return (new ServerRequest)->withParsedBody([
            'grant_type' => 'personal_access',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'user_id' => $userId,
            'scope' => implode(' ', $scopes),
        ]);
    }

    /**
     * Dispatch the given request to the authorization server.
     *
     * @param \Zend\Diactoros\ServerRequest $request
     *
     * @return array Token
     */
    protected function dispatchRequestToAuthorizationServer(ServerRequest $request)
    {
        $serverResponse = $this->server->respondToAccessTokenRequest($request, new Response);

        return json_decode($serverResponse->getBody()->__toString(), true);
    }

    /**
     * Extract ID from token.
     *
     * @param string $access_token
     *
     * @return string Token ID
     */
    protected function getTokenId($access_token)
    {
        return $this->jwt->parse($access_token)->getClaim('jti');
    }
}
