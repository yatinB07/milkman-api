<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Actions\Customer\Profile\ShowCustomerProfileAction;
use App\Actions\Customer\Profile\UpdateCustomerProfileAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\UpdateCustomerProfileRequest;
use App\Http\Resources\Customer\CustomerProfileResource;
use App\Models\Customer;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerProfileController extends Controller
{
    public function show(Request $request, IdentityAuthService $auth, ShowCustomerProfileAction $show): JsonResponse
    {
        $result = $show->execute($this->customer($request, $auth));

        return response()->json([
            'data' => new CustomerProfileResource($result['customer']),
            'referral' => $result['referral'],
        ]);
    }

    public function update(
        UpdateCustomerProfileRequest $request,
        IdentityAuthService $auth,
        UpdateCustomerProfileAction $update,
    ): JsonResponse {
        return response()->json([
            'message' => __('catalog.customer_profile_updated'),
            'data' => new CustomerProfileResource($update->execute($this->customer($request, $auth), $request->toData())),
        ]);
    }

    private function customer(Request $request, IdentityAuthService $auth): Customer
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'customer');

        return $identity;
    }
}
