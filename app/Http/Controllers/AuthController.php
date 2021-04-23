<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function signup()
    {
        $this->validate(request(), [
            'name' => ['required'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'min:8'],
        ]);

        $user = User::create([
            'name' => request('name'),
            'email' => request('email'),
            'password' => request('password'),
        ]);

        return response()->json([
            'message' => 'Successfully created user!'
        ], 201);
    }

    public function login()
    {
        $this->validate(request(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8'],
        ]);

        return $this->createToken(request('email'), request('password'));
    }

    public function createToken ($email, $password) {
        $credentials = array(
            'email' => $email,
            'password' => $password
        );

        if (!Auth::attempt($credentials)) {
            return 'login fail';
        }

        $data = [
            'grant_type' => 'password',
            'client_id' => '2',
            'client_secret' => 'EQchRWawXsQi3qBd4CTXpkOMAK37QruqdDSEUEc7',
            'username' => Auth::user()['email'],
            'password' => $password,
            'scope' => '*',
        ];
        $request = Request::create('/oauth/token', 'POST', $data);
        $response = app()->handle($request);

        return $response;
    }

    public function logout()
    {
        request()->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function user()
    {
        return response()->json(request()->user());
    }
}
