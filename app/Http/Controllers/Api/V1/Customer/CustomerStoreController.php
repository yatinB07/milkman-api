<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Actions\Customer\Stores\ListCustomerStoresAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CustomerStoreSearchRequest;
use App\Http\Resources\Customer\CustomerStoreResource;
use App\Models\Customer;
use App\Services\IdentityAuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerStoreController extends Controller
{
    public function index(
        CustomerStoreSearchRequest $request,
        IdentityAuthService $auth,
        ListCustomerStoresAction $stores,
    ): AnonymousResourceCollection {
        return CustomerStoreResource::collection($stores->execute($this->customer($request, $auth), $request->toData()));
    }

    private function customer(Request $request, IdentityAuthService $auth): Customer
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'customer');

        return $identity;
    }
}
