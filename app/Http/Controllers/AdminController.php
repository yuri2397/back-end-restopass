<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Laravel\Passport\Client;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class AdminController extends Controller
{
    private $client;

    public function __construct()
    {
        $this->client = Client::find(2);
    }

    public function login(Request $request){
        $request->validate([
            "email" => 'required|email',
            "password" => 'required'
        ]);

        $params = [
            'grant_type' => 'password',
            'client_id' => $this->client->id,
            'client_secret' => $this->client->secret,
            'username' => $request->email,
            'password' => $request->password,
            'scope' => '*',
        ];

        $request->request->add($params);

        $proxy = Request::create('/oauth/token', 'POST');

        return Route::dispatch($proxy);
    }

    public function logout(){
        $access_token = Auth::user()->token();
        DB::table('oauth_refresh_tokens')
        ->where('access_token_id', $access_token->id)
            ->update(['revoked' => true]);
        $access_token->revoke();
        return response()->json([], Response::HTTP_NO_CONTENT);
    }

    public function refresh(Request $request){
        $request->validate([
            'refresh_token' => 'required'
        ]);

        $params = [
            'grant_type' => 'refresh_token',
            'client_id' => $this->client->id,
            'client_secret' => $this->client->secret,
            'refresh_token' => $request->refresh_token
        ];

        $request->request->add($params);

        $proxy = Request::create('/oauth/token', 'POST');

        return Route::dispatch($proxy);
    }

    public function profile(){
        return Auth::user();
    }

    public function create(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'abilitie' => 'required'
        ]);

        $admin = new Admin();
        $admin->first_name = $request->first_name;
        $admin->last_name = $request->last_name;
        $admin->email = $request->email;
        $admin->password = bcrypt("restopass");

        return response()->json([
            'error' => false,
            'message' => 'Administrateur crée avec succés'
        ], 200);
    }
}
