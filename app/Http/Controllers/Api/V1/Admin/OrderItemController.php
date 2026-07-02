<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\OrderItems\CreateOrderItemAction;
use App\Actions\Admin\OrderItems\DeleteOrderItemAction;
use App\Actions\Admin\OrderItems\ListOrderItemsAction;
use App\Actions\Admin\OrderItems\ShowOrderItemAction;
use App\Actions\Admin\OrderItems\UpdateOrderItemAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListAdminResourcesRequest;
use App\Http\Requests\Admin\OrderItemRequest;
use App\Http\Requests\Admin\UpdateOrderItemRequest;
use App\Http\Resources\Admin\OrderItemResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderItemController extends Controller
{
    public function index(ListAdminResourcesRequest $request, IdentityAuthService $auth, ListOrderItemsAction $items): AnonymousResourceCollection
    {
        $this->authorizeOrderManagement($request, $auth);

        return OrderItemResource::collection($items->execute($request->toData()));
    }

    public function store(OrderItemRequest $request, IdentityAuthService $auth, CreateOrderItemAction $create): JsonResponse
    {
        $this->authorizeOrderManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.order_item_created'),
            'data' => new OrderItemResource($create->execute($request->toData())),
        ], 201);
    }

    public function show(Request $request, IdentityAuthService $auth, ShowOrderItemAction $show, int $orderItem): OrderItemResource
    {
        $this->authorizeOrderManagement($request, $auth);

        return new OrderItemResource($show->execute($orderItem));
    }

    public function update(UpdateOrderItemRequest $request, IdentityAuthService $auth, UpdateOrderItemAction $update, int $orderItem): JsonResponse
    {
        $this->authorizeOrderManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.order_item_updated'),
            'data' => new OrderItemResource($update->execute($orderItem, $request->toData())),
        ]);
    }

    public function destroy(Request $request, IdentityAuthService $auth, DeleteOrderItemAction $delete, int $orderItem): JsonResponse
    {
        $this->authorizeOrderManagement($request, $auth);
        $delete->execute($orderItem);

        return response()->json(['message' => __('catalog.order_item_deleted')]);
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
