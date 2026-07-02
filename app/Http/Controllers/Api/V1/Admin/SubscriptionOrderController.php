<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\SubscriptionOrders\CreateSubscriptionOrderAction;
use App\Actions\Admin\SubscriptionOrders\DeleteSubscriptionOrderAction;
use App\Actions\Admin\SubscriptionOrders\ListSubscriptionOrdersAction;
use App\Actions\Admin\SubscriptionOrders\ShowSubscriptionOrderAction;
use App\Actions\Admin\SubscriptionOrders\UpdateSubscriptionOrderAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListAdminResourcesRequest;
use App\Http\Requests\Admin\SubscriptionOrderRequest;
use App\Http\Requests\Admin\UpdateSubscriptionOrderRequest;
use App\Http\Resources\Admin\SubscriptionOrderResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SubscriptionOrderController extends Controller
{
    public function index(ListAdminResourcesRequest $request, IdentityAuthService $auth, ListSubscriptionOrdersAction $orders): AnonymousResourceCollection
    {
        $this->authorizeOrderManagement($request, $auth);

        return SubscriptionOrderResource::collection($orders->execute($request->toData()));
    }

    public function store(SubscriptionOrderRequest $request, IdentityAuthService $auth, CreateSubscriptionOrderAction $create): JsonResponse
    {
        $this->authorizeOrderManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.subscription_order_created'),
            'data' => new SubscriptionOrderResource($create->execute($request->toData())),
        ], 201);
    }

    public function show(Request $request, IdentityAuthService $auth, ShowSubscriptionOrderAction $show, int $subscriptionOrder): SubscriptionOrderResource
    {
        $this->authorizeOrderManagement($request, $auth);

        return new SubscriptionOrderResource($show->execute($subscriptionOrder));
    }

    public function update(UpdateSubscriptionOrderRequest $request, IdentityAuthService $auth, UpdateSubscriptionOrderAction $update, int $subscriptionOrder): JsonResponse
    {
        $this->authorizeOrderManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.subscription_order_updated'),
            'data' => new SubscriptionOrderResource($update->execute($subscriptionOrder, $request->toData())),
        ]);
    }

    public function destroy(Request $request, IdentityAuthService $auth, DeleteSubscriptionOrderAction $delete, int $subscriptionOrder): JsonResponse
    {
        $this->authorizeOrderManagement($request, $auth);
        $delete->execute($subscriptionOrder);

        return response()->json(['message' => __('catalog.subscription_order_deleted')]);
    }

    private function authorizeOrderManagement(Request $request, IdentityAuthService $auth): void
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'admin');

        if (! $identity->can('orders.update-status')) {
            throw new MissingPermissionException;
        }
    }
}
