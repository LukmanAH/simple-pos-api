<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuthenticateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(AuthenticateUserRequest $request)
    {
        $credential = $request->validated();
        $user = User::where('email', $credential['email'])->first();

        if (!$user || !Hash::check($credential['password'], $user->password)) {
            return ApiResponse::error(
                'Invalid Credential',
                Response::HTTP_UNAUTHORIZED
            );
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return ApiResponse::succes(
            [
                'token' => $token,
                'user' => new UserResource($user),
            ],
            'Login success',
            Response::HTTP_OK
        );
    }

    public function me(Request $request)
    {
        return ApiResponse::succes(
            new UserResource($request->user()),
            'User Data',
            Response::HTTP_OK
        );
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::succes(
            null,
            'Logout success',
            Response::HTTP_OK
        );
    }
}
