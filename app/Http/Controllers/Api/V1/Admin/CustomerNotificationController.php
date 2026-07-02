<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\CustomerNotifications\CreateCustomerNotificationAction;
use App\Actions\Admin\CustomerNotifications\DeleteCustomerNotificationAction;
use App\Actions\Admin\CustomerNotifications\ListCustomerNotificationsAction;
use App\Actions\Admin\CustomerNotifications\ShowCustomerNotificationAction;
use App\Actions\Admin\CustomerNotifications\UpdateCustomerNotificationAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomerNotificationRequest;
use App\Http\Requests\Admin\ListAdminResourcesRequest;
use App\Http\Requests\Admin\UpdateCustomerNotificationRequest;
use App\Http\Resources\Admin\CustomerNotificationResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerNotificationController extends Controller
{
    public function index(
        ListAdminResourcesRequest $request,
        IdentityAuthService $auth,
        ListCustomerNotificationsAction $notifications,
    ): AnonymousResourceCollection {
        $this->authorizeUserManagement($request, $auth);

        return CustomerNotificationResource::collection($notifications->execute($request->toData()));
    }

    public function store(
        CustomerNotificationRequest $request,
        IdentityAuthService $auth,
        CreateCustomerNotificationAction $create,
    ): JsonResponse {
        $this->authorizeUserManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.customer_notification_created'),
            'data' => new CustomerNotificationResource($create->execute($request->toData())),
        ], 201);
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowCustomerNotificationAction $show,
        int $customerNotification,
    ): CustomerNotificationResource {
        $this->authorizeUserManagement($request, $auth);

        return new CustomerNotificationResource($show->execute($customerNotification));
    }

    public function update(
        UpdateCustomerNotificationRequest $request,
        IdentityAuthService $auth,
        UpdateCustomerNotificationAction $update,
        int $customerNotification,
    ): JsonResponse {
        $this->authorizeUserManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.customer_notification_updated'),
            'data' => new CustomerNotificationResource($update->execute($customerNotification, $request->toData())),
        ]);
    }

    public function destroy(
        Request $request,
        IdentityAuthService $auth,
        DeleteCustomerNotificationAction $delete,
        int $customerNotification,
    ): JsonResponse {
        $this->authorizeUserManagement($request, $auth);
        $delete->execute($customerNotification);

        return response()->json([
            'message' => __('catalog.customer_notification_deleted'),
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
