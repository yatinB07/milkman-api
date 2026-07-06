<?php

namespace App\Http\Controllers\Api\V1\Rider;

use App\Actions\Rider\Notifications\ListRiderNotificationsAction;
use App\Actions\Rider\Notifications\ShowRiderNotificationAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Rider\ListRiderResourcesRequest;
use App\Http\Resources\Rider\RiderNotificationResource;
use App\Models\Rider;
use App\Services\IdentityAuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RiderNotificationController extends Controller
{
    public function index(
        ListRiderResourcesRequest $request,
        IdentityAuthService $auth,
        ListRiderNotificationsAction $notifications,
    ): AnonymousResourceCollection {
        return RiderNotificationResource::collection($notifications->execute($this->riderIdentity($request, $auth), $request->toData()));
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowRiderNotificationAction $show,
        int $notification,
    ): RiderNotificationResource {
        return new RiderNotificationResource($show->execute($this->riderIdentity($request, $auth), $notification));
    }

    private function riderIdentity(Request $request, IdentityAuthService $auth): Rider
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'rider');

        if (! $identity->can('orders.view')) {
            throw new MissingPermissionException;
        }

        return $identity;
    }
}
