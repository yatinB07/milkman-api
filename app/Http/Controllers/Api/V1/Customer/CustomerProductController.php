<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Actions\Customer\Products\ListCustomerStoreProductsAction;
use App\Actions\Customer\Products\ShowCustomerProductAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\ListCustomerResourcesRequest;
use App\Http\Resources\Customer\CustomerProductResource;
use App\Models\Customer;
use App\Services\IdentityAuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerProductController extends Controller
{
    public function index(
        ListCustomerResourcesRequest $request,
        int $store,
        IdentityAuthService $auth,
        ListCustomerStoreProductsAction $products,
    ): AnonymousResourceCollection {
        $this->customer($request, $auth);

        return CustomerProductResource::collection($products->execute($store, $request->toData()));
    }

    public function show(
        Request $request,
        int $product,
        IdentityAuthService $auth,
        ShowCustomerProductAction $productDetail,
    ): CustomerProductResource {
        $this->customer($request, $auth);

        return new CustomerProductResource($productDetail->execute($product));
    }

    private function customer(Request $request, IdentityAuthService $auth): Customer
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'customer');

        return $identity;
    }
}
