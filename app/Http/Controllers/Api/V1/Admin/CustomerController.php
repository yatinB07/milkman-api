<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\Customers\CreateCustomerAction;
use App\Actions\Admin\Customers\DeleteCustomerAction;
use App\Actions\Admin\Customers\ListCustomersAction;
use App\Actions\Admin\Customers\ShowCustomerAction;
use App\Actions\Admin\Customers\UpdateCustomerAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomerRequest;
use App\Http\Requests\Admin\ListAdminResourcesRequest;
use App\Http\Requests\Admin\UpdateCustomerRequest;
use App\Http\Resources\Admin\CustomerResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerController extends Controller
{
    public function index(
        ListAdminResourcesRequest $request,
        IdentityAuthService $auth,
        ListCustomersAction $customers,
    ): AnonymousResourceCollection {
        $this->authorizeCustomerManagement($request, $auth);

        return CustomerResource::collection($customers->execute($request->toData()));
    }

    public function store(CustomerRequest $request, IdentityAuthService $auth, CreateCustomerAction $create): JsonResponse
    {
        $this->authorizeCustomerManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.customer_created'),
            'data' => new CustomerResource($create->execute($request->toData())),
        ], 201);
    }

    public function show(Request $request, IdentityAuthService $auth, ShowCustomerAction $show, int $customer): CustomerResource
    {
        $this->authorizeCustomerManagement($request, $auth);

        return new CustomerResource($show->execute($customer));
    }

    public function update(
        UpdateCustomerRequest $request,
        IdentityAuthService $auth,
        UpdateCustomerAction $update,
        int $customer,
    ): JsonResponse {
        $this->authorizeCustomerManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.customer_updated'),
            'data' => new CustomerResource($update->execute($customer, $request->toData())),
        ]);
    }

    public function destroy(Request $request, IdentityAuthService $auth, DeleteCustomerAction $delete, int $customer): JsonResponse
    {
        $this->authorizeCustomerManagement($request, $auth);
        $delete->execute($customer);

        return response()->json([
            'message' => __('catalog.customer_deleted'),
        ]);
    }

    private function authorizeCustomerManagement(Request $request, IdentityAuthService $auth): void
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'admin');

        if (! $identity->can('users.manage')) {
            throw new MissingPermissionException;
        }
    }
}
