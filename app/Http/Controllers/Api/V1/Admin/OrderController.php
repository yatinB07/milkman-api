<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\Orders\CreateOrderAction;
use App\Actions\Admin\Orders\DeleteOrderAction;
use App\Actions\Admin\Orders\ListOrdersAction;
use App\Actions\Admin\Orders\ShowOrderAction;
use App\Actions\Admin\Orders\UpdateOrderAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListAdminResourcesRequest;
use App\Http\Requests\Admin\OrderRequest;
use App\Http\Requests\Admin\UpdateOrderRequest;
use App\Http\Resources\Admin\OrderResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function index(
        ListAdminResourcesRequest $request,
        IdentityAuthService $auth,
        ListOrdersAction $orders,
    ): AnonymousResourceCollection {
        $this->authorizeOrderManagement($request, $auth);

        return OrderResource::collection($orders->execute($request->toData()));
    }

    public function store(
        OrderRequest $request,
        IdentityAuthService $auth,
        CreateOrderAction $create,
    ): JsonResponse {
        $this->authorizeOrderManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.order_created'),
            'data' => new OrderResource($create->execute($request->toData())),
        ], 201);
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowOrderAction $show,
        int $order,
    ): OrderResource {
        $this->authorizeOrderManagement($request, $auth);

        return new OrderResource($show->execute($order));
    }

    public function update(
        UpdateOrderRequest $request,
        IdentityAuthService $auth,
        UpdateOrderAction $update,
        int $order,
    ): JsonResponse {
        $this->authorizeOrderManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.order_updated'),
            'data' => new OrderResource($update->execute($order, $request->toData())),
        ]);
    }

    public function destroy(
        Request $request,
        IdentityAuthService $auth,
        DeleteOrderAction $delete,
        int $order,
    ): JsonResponse {
        $this->authorizeOrderManagement($request, $auth);
        $delete->execute($order);

        return response()->json([
            'message' => __('catalog.order_deleted'),
        ]);
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
