<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Laravel\Passport\Client;

class AuthController extends Controller
{
    /**
     * Handle Login and Issue Tokens
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            return response()->json(['message' => __('Unauthorized')], 401);
        }

        $user = Auth::user();
        $user->update(['last_login_at' => now()]);

        // Internal Passport Token Issue (Password Grant)
        $client = Client::where('password_client', 1)->first();

        $authResponse = $response->json();

        if ($response->successful()) {
            $authResponse['user'] = [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ];
        }

        return response()->json($authResponse, $response->status());
    }

    /**
     * Refresh Access Token
     */
    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required',
        ]);

        $client = Client::where('password_client', 1)->first();

        $response = Http::asForm()->post(config('app.url') . '/oauth/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $request->refresh_token,
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'scope' => '',
        ]);

        return $response->json();
    }

    /**
     * Revoke Current Token
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json(['message' => __('Successfully logged out')]);
    }
}
