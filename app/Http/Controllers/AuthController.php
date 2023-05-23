<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['username'] = strstr($data['email'],'@',true);

        $user = User::create($data);
        $token = $user->createToken(User::USER_TOKEN);

        return $this->success([
            'user' => $user ,
            'token' => $token->plainTextToken,
        ],'user has been register successfully');
    }


    public function login(LoginRequest $request)
    {
        $isvalid = $this->isValidCredential($request);

        if(!$isvalid['success'])
        {
            return $this->error($isvalid['message'],Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $user = $isvalid['user'];
        $token = $user->createToken(User::USER_TOKEN);
        return $this->success([
            'user' => $user ,
            'token' => $token->plainTextToken,
            ],'user has been login successfully');

    }

    private function isValidCredential(LoginRequest $request)
    {
        $data = $request->validated();
        $user = User::where('email', $data['email'])->first();

        if ($user === null) {
            return [
                'success' => false,
                'message' => 'invalid Credential',
            ];
        }

        if(Hash::check($data['password'],$user->password)) {
            return [
                'success' => true,
                'user' => $user
            ];
        }
        return [
            'success' => false,
            'message' => 'password not matched',
        ];
    }


    public function loginWithToken()
    {
        return $this->success(auth()->user(),'login successfully');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(null,'logout successfully');
    }
}
