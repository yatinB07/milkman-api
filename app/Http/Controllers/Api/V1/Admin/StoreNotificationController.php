<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\StoreNotifications\CreateStoreNotificationAction;
use App\Actions\Admin\StoreNotifications\DeleteStoreNotificationAction;
use App\Actions\Admin\StoreNotifications\ListStoreNotificationsAction;
use App\Actions\Admin\StoreNotifications\ShowStoreNotificationAction;
use App\Actions\Admin\StoreNotifications\UpdateStoreNotificationAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListAdminResourcesRequest;
use App\Http\Requests\Admin\StoreNotificationRequest;
use App\Http\Requests\Admin\UpdateStoreNotificationRequest;
use App\Http\Resources\Admin\StoreNotificationResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StoreNotificationController extends Controller
{
    public function index(
        ListAdminResourcesRequest $request,
        IdentityAuthService $auth,
        ListStoreNotificationsAction $notifications,
    ): AnonymousResourceCollection {
        $this->authorizeStoreManagement($request, $auth);

        return StoreNotificationResource::collection($notifications->execute($request->toData()));
    }

    public function store(
        StoreNotificationRequest $request,
        IdentityAuthService $auth,
        CreateStoreNotificationAction $create,
    ): JsonResponse {
        $this->authorizeStoreManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.store_notification_created'),
            'data' => new StoreNotificationResource($create->execute($request->toData())),
        ], 201);
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowStoreNotificationAction $show,
        int $storeNotification,
    ): StoreNotificationResource {
        $this->authorizeStoreManagement($request, $auth);

        return new StoreNotificationResource($show->execute($storeNotification));
    }

    public function update(
        UpdateStoreNotificationRequest $request,
        IdentityAuthService $auth,
        UpdateStoreNotificationAction $update,
        int $storeNotification,
    ): JsonResponse {
        $this->authorizeStoreManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.store_notification_updated'),
            'data' => new StoreNotificationResource($update->execute($storeNotification, $request->toData())),
        ]);
    }

    public function destroy(
        Request $request,
        IdentityAuthService $auth,
        DeleteStoreNotificationAction $delete,
        int $storeNotification,
    ): JsonResponse {
        $this->authorizeStoreManagement($request, $auth);
        $delete->execute($storeNotification);

        return response()->json([
            'message' => __('catalog.store_notification_deleted'),
        ]);
    }

    private function authorizeStoreManagement(Request $request, IdentityAuthService $auth): void
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'admin');

        if (! $identity->can('stores.manage')) {
            throw new MissingPermissionException;
        }
    }
}
