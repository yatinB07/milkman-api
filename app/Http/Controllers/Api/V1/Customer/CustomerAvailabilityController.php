<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Actions\Customer\Auth\CheckCustomerEmailAvailabilityAction;
use App\Actions\Customer\Auth\CheckCustomerMobileAvailabilityAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CustomerEmailAvailabilityRequest;
use App\Http\Requests\Customer\CustomerMobileAvailabilityRequest;
use App\Http\Resources\Customer\CustomerAvailabilityResource;
use Illuminate\Http\JsonResponse;

class CustomerAvailabilityController extends Controller
{
    public function email(
        CustomerEmailAvailabilityRequest $request,
        CheckCustomerEmailAvailabilityAction $availability,
    ): JsonResponse {
        $result = $availability->execute($request->toData());

        return response()->json([
            'data' => new CustomerAvailabilityResource($result),
            'message' => $result['message'],
        ]);
    }

    public function mobile(
        CustomerMobileAvailabilityRequest $request,
        CheckCustomerMobileAvailabilityAction $availability,
    ): JsonResponse {
        $result = $availability->execute($request->toData());

        return response()->json([
            'data' => new CustomerAvailabilityResource($result),
            'message' => $result['message'],
        ]);
    }
}
