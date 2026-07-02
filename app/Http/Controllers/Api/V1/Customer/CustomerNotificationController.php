<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Actions\Customer\Notifications\ListCustomerNotificationsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\ListCustomerResourcesRequest;
use App\Http\Resources\Customer\CustomerNotificationResource;
use App\Models\Customer;
use App\Services\IdentityAuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerNotificationController extends Controller
{
    public function index(
        ListCustomerResourcesRequest $request,
        IdentityAuthService $auth,
        ListCustomerNotificationsAction $notifications,
    ): AnonymousResourceCollection {
        return CustomerNotificationResource::collection(
            $notifications->execute($this->customer($request, $auth), $request->toData()),
        );
    }

    private function customer(Request $request, IdentityAuthService $auth): Customer
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'customer');

        return $identity;
    }
}
