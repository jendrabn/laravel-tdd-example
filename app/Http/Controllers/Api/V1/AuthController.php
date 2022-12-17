<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $user = User::whereEmail($request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'The provided credentials are incorrect.'], 401);
        }

        $user->access_token = $user->createToken('access_token')->plainTextToken;

        return (new UserResource($user))->response()->setStatusCode(200);

        // $user = User::whereEmail($request->email)->first();

        // if (!$user || !Hash::check($request->password, $user->password)) {
        //     return response()->json(['message' => 'The provided credentials are incorrect.'], 401);
        // }

        // $accessToken = $user->createToken('access_token')->plainTextToken;

        // return response()->json([
        //     'data' => [
        //         'user' => [
        //             'id' => $user->id,
        //             'name' => $user->name,
        //             'email' => $user->email
        //         ],
        //         'access_token' => $accessToken
        //     ],
        // ], 200);

    }
}
