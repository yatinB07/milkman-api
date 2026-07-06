<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Actions\Customer\Auth\ResetCustomerPasswordAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CustomerPasswordResetRequest;
use Illuminate\Http\JsonResponse;

class CustomerPasswordResetController extends Controller
{
    public function store(CustomerPasswordResetRequest $request, ResetCustomerPasswordAction $reset): JsonResponse
    {
        $reset->execute($request->toData());

        return response()->json([
            'message' => __('auth.customer_password_reset'),
        ]);
    }
}
