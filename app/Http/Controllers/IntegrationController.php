<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IntegrationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * The integration / API guide, including the signed in user's API tokens.
     */
    public function index(Request $request)
    {
        $tokens = $request->user()->tokens()->orderBy('id', 'desc')->get();

        return view('integration.index', compact('tokens'));
    }

    /**
     * Issue a new Sanctum personal access token for the signed in user.
     * The plain text token is flashed to the session so the view can show it exactly once.
     */
    public function storeToken(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $token = $request->user()->createToken($request->input('name'));

        return redirect()->route('integration')
            ->with('plain_text_token', $token->plainTextToken)
            ->with('plain_text_token_name', $request->input('name'));
    }

    /**
     * Revoke one of the signed in user's tokens.
     */
    public function revokeToken(Request $request, $tokenId)
    {
        $deleted = $request->user()->tokens()->where('id', $tokenId)->delete();

        return redirect()->route('integration')
            ->with('token_status', $deleted ? 'Token revoked.' : 'That token could not be found.');
    }
}
