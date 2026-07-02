<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\MilkData\CreateMilkDataAction;
use App\Actions\Admin\MilkData\DeleteMilkDataAction;
use App\Actions\Admin\MilkData\ListMilkDataAction;
use App\Actions\Admin\MilkData\ShowMilkDataAction;
use App\Actions\Admin\MilkData\UpdateMilkDataAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListAdminResourcesRequest;
use App\Http\Requests\Admin\MilkDataRequest;
use App\Http\Requests\Admin\UpdateMilkDataRequest;
use App\Http\Resources\Admin\MilkDataResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MilkDataController extends Controller
{
    public function index(
        ListAdminResourcesRequest $request,
        IdentityAuthService $auth,
        ListMilkDataAction $milkData,
    ): AnonymousResourceCollection {
        $this->authorizeSettingsUpdate($request, $auth);

        return MilkDataResource::collection($milkData->execute($request->toData()));
    }

    public function store(
        MilkDataRequest $request,
        IdentityAuthService $auth,
        CreateMilkDataAction $create,
    ): JsonResponse {
        $this->authorizeSettingsUpdate($request, $auth);

        return response()->json([
            'message' => __('catalog.milk_data_created'),
            'data' => new MilkDataResource($create->execute($request->toData())),
        ], 201);
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowMilkDataAction $show,
        int $milkData,
    ): JsonResponse {
        $this->authorizeSettingsUpdate($request, $auth);

        return response()->json([
            'data' => new MilkDataResource($show->execute($milkData)),
        ]);
    }

    public function update(
        UpdateMilkDataRequest $request,
        IdentityAuthService $auth,
        UpdateMilkDataAction $update,
        int $milkData,
    ): JsonResponse {
        $this->authorizeSettingsUpdate($request, $auth);

        return response()->json([
            'message' => __('catalog.milk_data_updated'),
            'data' => new MilkDataResource($update->execute($milkData, $request->toData())),
        ]);
    }

    public function destroy(
        Request $request,
        IdentityAuthService $auth,
        DeleteMilkDataAction $delete,
        int $milkData,
    ): JsonResponse {
        $this->authorizeSettingsUpdate($request, $auth);
        $delete->execute($milkData);

        return response()->json([
            'message' => __('catalog.milk_data_deleted'),
        ]);
    }

    private function authorizeSettingsUpdate(Request $request, IdentityAuthService $auth): void
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'admin');

        if (! $identity->can('settings.update')) {
            throw new MissingPermissionException;
        }
    }
}
