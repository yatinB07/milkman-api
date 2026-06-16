<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\LoginIdentityAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginIdentityRequest;
use App\Http\Resources\Auth\IdentityProfileResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IdentityAuthController extends Controller
{
    public function login(LoginIdentityRequest $request, LoginIdentityAction $login, string $identityType): JsonResponse
    {
        $result = $login->execute(
            $identityType,
            $request->string('email')->toString(),
            $request->string('password')->toString(),
        );

        return response()->json([
            'data' => [
                'token' => $result['token'],
                'user' => new IdentityProfileResource($result['identity'], $identityType),
            ],
        ]);
    }

    public function me(Request $request, IdentityAuthService $auth, string $identityType): JsonResponse
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, $identityType);

        return response()->json([
            'data' => [
                'user' => new IdentityProfileResource($identity, $identityType),
            ],
        ]);
    }

    public function logout(Request $request, IdentityAuthService $auth, string $identityType): JsonResponse
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, $identityType);
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => __('auth.logged_out'),
        ]);
    }
}
