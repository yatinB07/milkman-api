<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\DeliveryOptions\CreateDeliveryOptionAction;
use App\Actions\Admin\DeliveryOptions\DeleteDeliveryOptionAction;
use App\Actions\Admin\DeliveryOptions\ListDeliveryOptionsAction;
use App\Actions\Admin\DeliveryOptions\ShowDeliveryOptionAction;
use App\Actions\Admin\DeliveryOptions\UpdateDeliveryOptionAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeliveryOptionRequest;
use App\Http\Requests\Admin\ListAdminResourcesRequest;
use App\Http\Requests\Admin\UpdateDeliveryOptionRequest;
use App\Http\Resources\Admin\DeliveryOptionResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DeliveryOptionController extends Controller
{
    public function index(
        ListAdminResourcesRequest $request,
        IdentityAuthService $auth,
        ListDeliveryOptionsAction $deliveryOptions,
    ): AnonymousResourceCollection {
        $this->authorizeDeliveryOptionManagement($request, $auth);

        return DeliveryOptionResource::collection($deliveryOptions->execute($request->toData()));
    }

    public function store(
        DeliveryOptionRequest $request,
        IdentityAuthService $auth,
        CreateDeliveryOptionAction $create,
    ): JsonResponse {
        $this->authorizeDeliveryOptionManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.delivery_option_created'),
            'data' => new DeliveryOptionResource($create->execute($request->toData())),
        ], 201);
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowDeliveryOptionAction $show,
        int $deliveryOption,
    ): DeliveryOptionResource {
        $this->authorizeDeliveryOptionManagement($request, $auth);

        return new DeliveryOptionResource($show->execute($deliveryOption));
    }

    public function update(
        UpdateDeliveryOptionRequest $request,
        IdentityAuthService $auth,
        UpdateDeliveryOptionAction $update,
        int $deliveryOption,
    ): JsonResponse {
        $this->authorizeDeliveryOptionManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.delivery_option_updated'),
            'data' => new DeliveryOptionResource($update->execute($deliveryOption, $request->toData())),
        ]);
    }

    public function destroy(
        Request $request,
        IdentityAuthService $auth,
        DeleteDeliveryOptionAction $delete,
        int $deliveryOption,
    ): JsonResponse {
        $this->authorizeDeliveryOptionManagement($request, $auth);
        $delete->execute($deliveryOption);

        return response()->json([
            'message' => __('catalog.delivery_option_deleted'),
        ]);
    }

    private function authorizeDeliveryOptionManagement(Request $request, IdentityAuthService $auth): void
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'admin');

        if (! $identity->can('stores.manage')) {
            throw new MissingPermissionException;
        }
    }
}
