<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\SubscriptionOrderItems\CreateSubscriptionOrderItemAction;
use App\Actions\Admin\SubscriptionOrderItems\DeleteSubscriptionOrderItemAction;
use App\Actions\Admin\SubscriptionOrderItems\ListSubscriptionOrderItemsAction;
use App\Actions\Admin\SubscriptionOrderItems\ShowSubscriptionOrderItemAction;
use App\Actions\Admin\SubscriptionOrderItems\UpdateSubscriptionOrderItemAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListAdminResourcesRequest;
use App\Http\Requests\Admin\SubscriptionOrderItemRequest;
use App\Http\Requests\Admin\UpdateSubscriptionOrderItemRequest;
use App\Http\Resources\Admin\SubscriptionOrderItemResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SubscriptionOrderItemController extends Controller
{
    public function index(ListAdminResourcesRequest $request, IdentityAuthService $auth, ListSubscriptionOrderItemsAction $items): AnonymousResourceCollection
    {
        $this->authorizeOrderManagement($request, $auth);

        return SubscriptionOrderItemResource::collection($items->execute($request->toData()));
    }

    public function store(SubscriptionOrderItemRequest $request, IdentityAuthService $auth, CreateSubscriptionOrderItemAction $create): JsonResponse
    {
        $this->authorizeOrderManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.subscription_order_item_created'),
            'data' => new SubscriptionOrderItemResource($create->execute($request->toData())),
        ], 201);
    }

    public function show(Request $request, IdentityAuthService $auth, ShowSubscriptionOrderItemAction $show, int $subscriptionOrderItem): SubscriptionOrderItemResource
    {
        $this->authorizeOrderManagement($request, $auth);

        return new SubscriptionOrderItemResource($show->execute($subscriptionOrderItem));
    }

    public function update(UpdateSubscriptionOrderItemRequest $request, IdentityAuthService $auth, UpdateSubscriptionOrderItemAction $update, int $subscriptionOrderItem): JsonResponse
    {
        $this->authorizeOrderManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.subscription_order_item_updated'),
            'data' => new SubscriptionOrderItemResource($update->execute($subscriptionOrderItem, $request->toData())),
        ]);
    }

    public function destroy(Request $request, IdentityAuthService $auth, DeleteSubscriptionOrderItemAction $delete, int $subscriptionOrderItem): JsonResponse
    {
        $this->authorizeOrderManagement($request, $auth);
        $delete->execute($subscriptionOrderItem);

        return response()->json(['message' => __('catalog.subscription_order_item_deleted')]);
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
