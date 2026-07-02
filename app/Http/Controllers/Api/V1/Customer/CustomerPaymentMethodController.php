<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Actions\Customer\PaymentMethods\ListCustomerPaymentMethodsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\ListCustomerResourcesRequest;
use App\Http\Resources\Customer\PaymentMethodResource;
use App\Models\Customer;
use App\Services\IdentityAuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerPaymentMethodController extends Controller
{
    public function index(
        ListCustomerResourcesRequest $request,
        IdentityAuthService $auth,
        ListCustomerPaymentMethodsAction $methods,
    ): AnonymousResourceCollection {
        $this->customer($request, $auth);

        return PaymentMethodResource::collection($methods->execute($request->toData()));
    }

    private function customer(Request $request, IdentityAuthService $auth): Customer
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'customer');

        return $identity;
    }
}
