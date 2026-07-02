<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\CustomerAddresses\CreateCustomerAddressAction;
use App\Actions\Admin\CustomerAddresses\DeleteCustomerAddressAction;
use App\Actions\Admin\CustomerAddresses\ListCustomerAddressesAction;
use App\Actions\Admin\CustomerAddresses\ShowCustomerAddressAction;
use App\Actions\Admin\CustomerAddresses\UpdateCustomerAddressAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomerAddressRequest;
use App\Http\Requests\Admin\ListAdminResourcesRequest;
use App\Http\Requests\Admin\UpdateCustomerAddressRequest;
use App\Http\Resources\Admin\CustomerAddressResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerAddressController extends Controller
{
    public function index(
        ListAdminResourcesRequest $request,
        IdentityAuthService $auth,
        ListCustomerAddressesAction $addresses,
    ): AnonymousResourceCollection {
        $this->authorizeUserManagement($request, $auth);

        return CustomerAddressResource::collection($addresses->execute($request->toData()));
    }

    public function store(
        CustomerAddressRequest $request,
        IdentityAuthService $auth,
        CreateCustomerAddressAction $create,
    ): JsonResponse {
        $this->authorizeUserManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.customer_address_created'),
            'data' => new CustomerAddressResource($create->execute($request->toData())),
        ], 201);
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowCustomerAddressAction $show,
        int $customerAddress,
    ): CustomerAddressResource {
        $this->authorizeUserManagement($request, $auth);

        return new CustomerAddressResource($show->execute($customerAddress));
    }

    public function update(
        UpdateCustomerAddressRequest $request,
        IdentityAuthService $auth,
        UpdateCustomerAddressAction $update,
        int $customerAddress,
    ): JsonResponse {
        $this->authorizeUserManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.customer_address_updated'),
            'data' => new CustomerAddressResource($update->execute($customerAddress, $request->toData())),
        ]);
    }

    public function destroy(
        Request $request,
        IdentityAuthService $auth,
        DeleteCustomerAddressAction $delete,
        int $customerAddress,
    ): JsonResponse {
        $this->authorizeUserManagement($request, $auth);
        $delete->execute($customerAddress);

        return response()->json([
            'message' => __('catalog.customer_address_deleted'),
        ]);
    }

    private function authorizeUserManagement(Request $request, IdentityAuthService $auth): void
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'admin');

        if (! $identity->can('users.manage')) {
            throw new MissingPermissionException;
        }
    }
}
