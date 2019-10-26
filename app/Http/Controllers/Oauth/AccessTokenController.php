<?php

namespace App\Http\Controllers\Oauth;

use App\Models\Token;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Models\User;
use Zend\Diactoros\Response as Psr7Response;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;

class AccessTokenController extends Controller
{
    /**
     * The authorization server instance.
     *
     * @var \League\OAuth2\Server\AuthorizationServer
     */
    protected $server;

    /**
     * Constructor.
     *
     * @param \League\OAuth2\Server\AuthorizationServer $server
     */
    public function __construct(AuthorizationServer $server)
    {
        $this->middleware('api');
        $this->server = $server;
    }

    /**
     * Issue new access token.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return \Illuminate\Http\Response
     */
    public function issueToken(Request $request)
    {
        $this->validate($request, [
            'client_id' => 'sometimes|uuid',
            'client_secret' => 'sometimes',
            'grant_type' => ['required', Rule::in(['authorization_code', 'client_credentials', 'password', 'refresh_token'])],
            'code' => 'required_if:grant_type,authorization_code',
            'redirect_uri' => 'nullable|url',
            'refresh_token' => 'required_if:grant_type,refresh_token',
            'username' => 'required_if:grant_type,password',
            'password' => 'required_if:grant_type,password',
            'scopes' => 'sometimes|array',
        ]);

        $serverRequest = new \Zend\Diactoros\ServerRequest();

        $serverRequest = $serverRequest->withParsedBody([
            'grant_type' => $request->grant_type,
            'client_id' => $request->client_id,
            'client_secret' => $request->client_secret,
            'code' => $request->code,
            'redirect_uri' => $request->redirect_uri,
            'refresh_token' => $request->refresh_token,
            'username' => $request->username,
            'password' => $request->password,
            'scope' => implode(' ', (array) $request->scopes),
        ]);

        try {
            $serverResponse = $this->server->respondToAccessTokenRequest($serverRequest, new Psr7Response);
        } catch (OAuthServerException $e) {
            return new Response(['error' => $e->getMessage()], 401);
        }

        $token = json_decode($serverResponse->getBody(), true);

        if ($request->grant_type === 'password') {
            $token['user'] = User::where('email', $request->username)->first();
        }

        return new Response(
            $token,
            $serverResponse->getStatusCode(),
            $serverResponse->getHeaders()
        );
    }
}
