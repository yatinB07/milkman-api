<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Actions\Customer\Addresses\CreateCustomerAddressAction;
use App\Actions\Customer\Addresses\DeleteCustomerAddressAction;
use App\Actions\Customer\Addresses\ListCustomerAddressesAction;
use App\Actions\Customer\Addresses\ShowCustomerAddressAction;
use App\Actions\Customer\Addresses\UpdateCustomerAddressAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CustomerAddressRequest;
use App\Http\Requests\Customer\ListCustomerResourcesRequest;
use App\Http\Requests\Customer\UpdateCustomerAddressRequest;
use App\Http\Resources\Customer\CustomerAddressResource;
use App\Models\Customer;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerAddressController extends Controller
{
    public function index(
        ListCustomerResourcesRequest $request,
        IdentityAuthService $auth,
        ListCustomerAddressesAction $addresses,
    ): AnonymousResourceCollection {
        return CustomerAddressResource::collection(
            $addresses->execute($this->customer($request, $auth), $request->toData()),
        );
    }

    public function store(
        CustomerAddressRequest $request,
        IdentityAuthService $auth,
        CreateCustomerAddressAction $create,
    ): JsonResponse {
        return response()->json([
            'message' => __('catalog.customer_address_saved'),
            'data' => new CustomerAddressResource($create->execute($this->customer($request, $auth), $request->toData())),
        ], 201);
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowCustomerAddressAction $show,
        int $address,
    ): CustomerAddressResource {
        return new CustomerAddressResource($show->execute($this->customer($request, $auth), $address));
    }

    public function update(
        UpdateCustomerAddressRequest $request,
        IdentityAuthService $auth,
        UpdateCustomerAddressAction $update,
        int $address,
    ): JsonResponse {
        return response()->json([
            'message' => __('catalog.customer_address_updated_short'),
            'data' => new CustomerAddressResource($update->execute($this->customer($request, $auth), $address, $request->toData())),
        ]);
    }

    public function destroy(
        Request $request,
        IdentityAuthService $auth,
        DeleteCustomerAddressAction $delete,
        int $address,
    ): JsonResponse {
        $delete->execute($this->customer($request, $auth), $address);

        return response()->json([
            'message' => __('catalog.customer_address_removed'),
        ]);
    }

    private function customer(Request $request, IdentityAuthService $auth): Customer
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'customer');

        return $identity;
    }
}
