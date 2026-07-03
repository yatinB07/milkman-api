<?php

namespace App\Http\Controllers\Api\V1\Store;

use App\Actions\Store\Notifications\ListStoreNotificationsAction;
use App\Actions\Store\Notifications\ShowStoreNotificationAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\ListStoreResourcesRequest;
use App\Http\Resources\Store\StoreNotificationResource;
use App\Models\Store;
use App\Services\IdentityAuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StoreNotificationController extends Controller
{
    public function index(
        ListStoreResourcesRequest $request,
        IdentityAuthService $auth,
        ListStoreNotificationsAction $notifications,
    ): AnonymousResourceCollection {
        return StoreNotificationResource::collection($notifications->execute($this->storeIdentity($request, $auth), $request->toData()));
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowStoreNotificationAction $show,
        int $notification,
    ): StoreNotificationResource {
        return new StoreNotificationResource($show->execute($this->storeIdentity($request, $auth), $notification));
    }

    private function storeIdentity(Request $request, IdentityAuthService $auth): Store
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'store');

        if (! $identity->can('stores.view')) {
            throw new MissingPermissionException;
        }

        return $identity;
    }
}
