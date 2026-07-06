<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Actions\Customer\Pages\ListCustomerPagesAction;
use App\Actions\Customer\Pages\ShowCustomerPageAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\ListCustomerResourcesRequest;
use App\Http\Resources\Customer\CustomerPageResource;
use App\Models\Customer;
use App\Services\IdentityAuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerPageController extends Controller
{
    public function index(
        ListCustomerResourcesRequest $request,
        IdentityAuthService $auth,
        ListCustomerPagesAction $pages,
    ): AnonymousResourceCollection {
        $this->customer($request, $auth);

        return CustomerPageResource::collection($pages->execute($request->toData()));
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowCustomerPageAction $show,
        int $page,
    ): CustomerPageResource {
        $this->customer($request, $auth);

        return new CustomerPageResource($show->execute($page));
    }

    private function customer(Request $request, IdentityAuthService $auth): Customer
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'customer');

        return $identity;
    }
}
