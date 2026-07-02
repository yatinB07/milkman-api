<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\Zones\CreateZoneAction;
use App\Actions\Admin\Zones\DeleteZoneAction;
use App\Actions\Admin\Zones\ListZonesAction;
use App\Actions\Admin\Zones\ShowZoneAction;
use App\Actions\Admin\Zones\UpdateZoneAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListAdminResourcesRequest;
use App\Http\Requests\Admin\UpdateZoneRequest;
use App\Http\Requests\Admin\ZoneRequest;
use App\Http\Resources\Admin\ZoneResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ZoneController extends Controller
{
    public function index(
        ListAdminResourcesRequest $request,
        IdentityAuthService $auth,
        ListZonesAction $zones,
    ): AnonymousResourceCollection {
        $this->authorizeZoneManagement($request, $auth);

        return ZoneResource::collection($zones->execute($request->toData()));
    }

    public function store(
        ZoneRequest $request,
        IdentityAuthService $auth,
        CreateZoneAction $create,
    ): JsonResponse {
        $this->authorizeZoneManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.zone_created'),
            'data' => new ZoneResource($create->execute($request->toData())),
        ], 201);
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowZoneAction $show,
        int $zone,
    ): ZoneResource {
        $this->authorizeZoneManagement($request, $auth);

        return new ZoneResource($show->execute($zone));
    }

    public function update(
        UpdateZoneRequest $request,
        IdentityAuthService $auth,
        UpdateZoneAction $update,
        int $zone,
    ): JsonResponse {
        $this->authorizeZoneManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.zone_updated'),
            'data' => new ZoneResource($update->execute($zone, $request->toData())),
        ]);
    }

    public function destroy(
        Request $request,
        IdentityAuthService $auth,
        DeleteZoneAction $delete,
        int $zone,
    ): JsonResponse {
        $this->authorizeZoneManagement($request, $auth);
        $delete->execute($zone);

        return response()->json([
            'message' => __('catalog.zone_deleted'),
        ]);
    }

    private function authorizeZoneManagement(Request $request, IdentityAuthService $auth): void
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'admin');

        if (! $identity->can('settings.update')) {
            throw new MissingPermissionException;
        }
    }
}
