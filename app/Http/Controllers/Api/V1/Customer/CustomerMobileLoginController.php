<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Actions\Customer\Auth\LoginCustomerByMobileAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CustomerMobileLoginRequest;
use App\Http\Resources\Auth\IdentityProfileResource;
use Illuminate\Http\JsonResponse;

class CustomerMobileLoginController extends Controller
{
    public function store(CustomerMobileLoginRequest $request, LoginCustomerByMobileAction $login): JsonResponse
    {
        $result = $login->execute($request->toData());

        return response()->json([
            'data' => [
                'token' => $result['token'],
                'user' => new IdentityProfileResource($result['identity'], 'customer'),
            ],
        ]);
    }
}
