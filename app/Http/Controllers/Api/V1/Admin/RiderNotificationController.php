<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\RiderNotifications\CreateRiderNotificationAction;
use App\Actions\Admin\RiderNotifications\DeleteRiderNotificationAction;
use App\Actions\Admin\RiderNotifications\ListRiderNotificationsAction;
use App\Actions\Admin\RiderNotifications\ShowRiderNotificationAction;
use App\Actions\Admin\RiderNotifications\UpdateRiderNotificationAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListAdminResourcesRequest;
use App\Http\Requests\Admin\RiderNotificationRequest;
use App\Http\Requests\Admin\UpdateRiderNotificationRequest;
use App\Http\Resources\Admin\RiderNotificationResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RiderNotificationController extends Controller
{
    public function index(
        ListAdminResourcesRequest $request,
        IdentityAuthService $auth,
        ListRiderNotificationsAction $notifications,
    ): AnonymousResourceCollection {
        $this->authorizeRiderManagement($request, $auth);

        return RiderNotificationResource::collection($notifications->execute($request->toData()));
    }

    public function store(
        RiderNotificationRequest $request,
        IdentityAuthService $auth,
        CreateRiderNotificationAction $create,
    ): JsonResponse {
        $this->authorizeRiderManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.rider_notification_created'),
            'data' => new RiderNotificationResource($create->execute($request->toData())),
        ], 201);
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowRiderNotificationAction $show,
        int $riderNotification,
    ): RiderNotificationResource {
        $this->authorizeRiderManagement($request, $auth);

        return new RiderNotificationResource($show->execute($riderNotification));
    }

    public function update(
        UpdateRiderNotificationRequest $request,
        IdentityAuthService $auth,
        UpdateRiderNotificationAction $update,
        int $riderNotification,
    ): JsonResponse {
        $this->authorizeRiderManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.rider_notification_updated'),
            'data' => new RiderNotificationResource($update->execute($riderNotification, $request->toData())),
        ]);
    }

    public function destroy(
        Request $request,
        IdentityAuthService $auth,
        DeleteRiderNotificationAction $delete,
        int $riderNotification,
    ): JsonResponse {
        $this->authorizeRiderManagement($request, $auth);
        $delete->execute($riderNotification);

        return response()->json([
            'message' => __('catalog.rider_notification_deleted'),
        ]);
    }

    private function authorizeRiderManagement(Request $request, IdentityAuthService $auth): void
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'admin');

        if (! $identity->can('riders.manage')) {
            throw new MissingPermissionException;
        }
    }
}
