<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Actions\Customer\Availability\ListCustomerDeliveryOptionsAction;
use App\Actions\Customer\Availability\ListCustomerTimeSlotsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\ListCustomerResourcesRequest;
use App\Http\Resources\Customer\DeliveryOptionResource;
use App\Http\Resources\Customer\TimeSlotResource;
use App\Models\Customer;
use App\Services\IdentityAuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerStoreAvailabilityController extends Controller
{
    public function deliveryOptions(
        ListCustomerResourcesRequest $request,
        int $store,
        IdentityAuthService $auth,
        ListCustomerDeliveryOptionsAction $deliveryOptions,
    ): AnonymousResourceCollection {
        $this->customer($request, $auth);

        return DeliveryOptionResource::collection($deliveryOptions->execute($store, $request->toData()));
    }

    public function timeSlots(
        ListCustomerResourcesRequest $request,
        int $store,
        IdentityAuthService $auth,
        ListCustomerTimeSlotsAction $timeSlots,
    ): AnonymousResourceCollection {
        $this->customer($request, $auth);

        return TimeSlotResource::collection($timeSlots->execute($store, $request->toData()));
    }

    private function customer(Request $request, IdentityAuthService $auth): Customer
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'customer');

        return $identity;
    }
}
