<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Actions\Customer\Cart\ShowCustomerCartDataAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CustomerCartDataRequest;
use App\Http\Resources\Customer\CustomerCartDataResource;
use App\Models\Customer;
use App\Services\IdentityAuthService;
use Illuminate\Http\Request;

class CustomerCartController extends Controller
{
    public function show(
        CustomerCartDataRequest $request,
        int $store,
        IdentityAuthService $auth,
        ShowCustomerCartDataAction $cartData,
    ): CustomerCartDataResource {
        $this->customer($request, $auth);

        return new CustomerCartDataResource($cartData->execute($store, $request->toData()));
    }

    private function customer(Request $request, IdentityAuthService $auth): Customer
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'customer');

        return $identity;
    }
}
