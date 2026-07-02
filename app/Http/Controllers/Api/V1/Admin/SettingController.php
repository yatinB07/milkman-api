<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\Settings\CreateSettingAction;
use App\Actions\Admin\Settings\DeleteSettingAction;
use App\Actions\Admin\Settings\ListSettingsAction;
use App\Actions\Admin\Settings\ShowSettingAction;
use App\Actions\Admin\Settings\UpdateSettingAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListAdminResourcesRequest;
use App\Http\Requests\Admin\SettingRequest;
use App\Http\Requests\Admin\UpdateSettingRequest;
use App\Http\Resources\Admin\SettingResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SettingController extends Controller
{
    public function index(
        ListAdminResourcesRequest $request,
        IdentityAuthService $auth,
        ListSettingsAction $settings,
    ): AnonymousResourceCollection {
        $this->authorizeSettingsUpdate($request, $auth);

        return SettingResource::collection($settings->execute($request->toData()));
    }

    public function store(
        SettingRequest $request,
        IdentityAuthService $auth,
        CreateSettingAction $create,
    ): JsonResponse {
        $this->authorizeSettingsUpdate($request, $auth);

        return response()->json([
            'message' => __('catalog.setting_created'),
            'data' => new SettingResource($create->execute($request->toData())),
        ], 201);
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowSettingAction $show,
        int $setting,
    ): SettingResource {
        $this->authorizeSettingsUpdate($request, $auth);

        return new SettingResource($show->execute($setting));
    }

    public function update(
        UpdateSettingRequest $request,
        IdentityAuthService $auth,
        UpdateSettingAction $update,
        int $setting,
    ): JsonResponse {
        $this->authorizeSettingsUpdate($request, $auth);

        return response()->json([
            'message' => __('catalog.setting_updated'),
            'data' => new SettingResource($update->execute($setting, $request->toData())),
        ]);
    }

    public function destroy(
        Request $request,
        IdentityAuthService $auth,
        DeleteSettingAction $delete,
        int $setting,
    ): JsonResponse {
        $this->authorizeSettingsUpdate($request, $auth);
        $delete->execute($setting);

        return response()->json([
            'message' => __('catalog.setting_deleted'),
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
