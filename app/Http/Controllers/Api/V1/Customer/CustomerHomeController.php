<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Actions\Customer\Home\ShowCustomerHomeAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CustomerHomeRequest;
use App\Http\Resources\Customer\CustomerHomeResource;
use App\Models\Customer;
use App\Services\IdentityAuthService;
use Illuminate\Http\Request;

class CustomerHomeController extends Controller
{
    public function show(
        CustomerHomeRequest $request,
        IdentityAuthService $auth,
        ShowCustomerHomeAction $home,
    ): CustomerHomeResource {
        return new CustomerHomeResource($home->execute($this->customer($request, $auth), $request->toData()));
    }

    private function customer(Request $request, IdentityAuthService $auth): Customer
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'customer');

        return $identity;
    }
}
