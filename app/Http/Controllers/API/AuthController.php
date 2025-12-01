<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrationRequest;
use App\Http\Resources\AuthResource;
use App\Http\Resources\LoginResource;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(RegistrationRequest $request)
    {
        try{
            $user = \App\Models\User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'phone' => $request->phone,
                'role' => $request->role ?? 'customer',
            ]);

            $message = 'Registration successful';
            return $this->respondWithMessageAndPayload(new AuthResource($user), $message);
        }catch(\Exception $ex){
            dd($ex);
            return $this->respondBadRequest($ex->getMessage());
        }
    }

    public function login(Request $request)
    {
        try{
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string|min:8',
            ]);

            $credentials = $request->only('email', 'password');

            if (!auth()->attempt($credentials)) {
                return $this->respondUnauthorized('Invalid credentials');
            }

            $user = auth()->user();
            $user->accessToken = $user->createToken('authToken')->plainTextToken;
            $message = 'Login successful';

            return $this->respondWithMessageAndPayload(new LoginResource($user), $message);
        }catch(\Exception $ex){
            return $this->respondBadRequest($ex->getMessage());
        }
    }

    public function logout(Request $request)
    {
        try{
            $user = $request->user();
            $user->tokens()->delete();

            return $this->respondWithMessage('Logout successful');
        }catch(\Exception $ex){
            return $this->respondBadRequest($ex->getMessage());
        }
    }
}