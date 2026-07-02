<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\Riders\CreateRiderAction;
use App\Actions\Admin\Riders\DeleteRiderAction;
use App\Actions\Admin\Riders\ListRidersAction;
use App\Actions\Admin\Riders\ShowRiderAction;
use App\Actions\Admin\Riders\UpdateRiderAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListAdminResourcesRequest;
use App\Http\Requests\Admin\RiderRequest;
use App\Http\Requests\Admin\UpdateRiderRequest;
use App\Http\Resources\Admin\RiderResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RiderController extends Controller
{
    public function index(
        ListAdminResourcesRequest $request,
        IdentityAuthService $auth,
        ListRidersAction $riders,
    ): AnonymousResourceCollection {
        $this->authorizeRiderManagement($request, $auth);

        return RiderResource::collection($riders->execute($request->toData()));
    }

    public function store(RiderRequest $request, IdentityAuthService $auth, CreateRiderAction $create): JsonResponse
    {
        $this->authorizeRiderManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.rider_created'),
            'data' => new RiderResource($create->execute($request->toData())),
        ], 201);
    }

    public function show(Request $request, IdentityAuthService $auth, ShowRiderAction $show, int $rider): RiderResource
    {
        $this->authorizeRiderManagement($request, $auth);

        return new RiderResource($show->execute($rider));
    }

    public function update(
        UpdateRiderRequest $request,
        IdentityAuthService $auth,
        UpdateRiderAction $update,
        int $rider,
    ): JsonResponse {
        $this->authorizeRiderManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.rider_updated'),
            'data' => new RiderResource($update->execute($rider, $request->toData())),
        ]);
    }

    public function destroy(Request $request, IdentityAuthService $auth, DeleteRiderAction $delete, int $rider): JsonResponse
    {
        $this->authorizeRiderManagement($request, $auth);
        $delete->execute($rider);

        return response()->json([
            'message' => __('catalog.rider_deleted'),
        ]);
    }

    private function authorizeRiderManagement(Request $request, IdentityAuthService $auth): void
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'admin');

        if (! $identity->can('riders.manage')) {
            throw new MissingPermissionException;
        }
    }
}
