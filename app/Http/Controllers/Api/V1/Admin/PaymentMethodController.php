<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\PaymentMethods\CreatePaymentMethodAction;
use App\Actions\Admin\PaymentMethods\DeletePaymentMethodAction;
use App\Actions\Admin\PaymentMethods\ListPaymentMethodsAction;
use App\Actions\Admin\PaymentMethods\ShowPaymentMethodAction;
use App\Actions\Admin\PaymentMethods\UpdatePaymentMethodAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListAdminResourcesRequest;
use App\Http\Requests\Admin\PaymentMethodRequest;
use App\Http\Requests\Admin\UpdatePaymentMethodRequest;
use App\Http\Resources\Admin\PaymentMethodResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentMethodController extends Controller
{
    public function index(
        ListAdminResourcesRequest $request,
        IdentityAuthService $auth,
        ListPaymentMethodsAction $paymentMethods,
    ): AnonymousResourceCollection {
        $this->authorizePaymentMethodManagement($request, $auth);

        return PaymentMethodResource::collection($paymentMethods->execute($request->toData()));
    }

    public function store(
        PaymentMethodRequest $request,
        IdentityAuthService $auth,
        CreatePaymentMethodAction $create,
    ): JsonResponse {
        $this->authorizePaymentMethodManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.payment_method_created'),
            'data' => new PaymentMethodResource($create->execute($request->toData())),
        ], 201);
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowPaymentMethodAction $show,
        int $paymentMethod,
    ): PaymentMethodResource {
        $this->authorizePaymentMethodManagement($request, $auth);

        return new PaymentMethodResource($show->execute($paymentMethod));
    }

    public function update(
        UpdatePaymentMethodRequest $request,
        IdentityAuthService $auth,
        UpdatePaymentMethodAction $update,
        int $paymentMethod,
    ): JsonResponse {
        $this->authorizePaymentMethodManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.payment_method_updated'),
            'data' => new PaymentMethodResource($update->execute($paymentMethod, $request->toData())),
        ]);
    }

    public function destroy(
        Request $request,
        IdentityAuthService $auth,
        DeletePaymentMethodAction $delete,
        int $paymentMethod,
    ): JsonResponse {
        $this->authorizePaymentMethodManagement($request, $auth);
        $delete->execute($paymentMethod);

        return response()->json([
            'message' => __('catalog.payment_method_deleted'),
        ]);
    }

    private function authorizePaymentMethodManagement(Request $request, IdentityAuthService $auth): void
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'admin');

        if (! $identity->can('settings.update')) {
            throw new MissingPermissionException;
        }
    }
}
