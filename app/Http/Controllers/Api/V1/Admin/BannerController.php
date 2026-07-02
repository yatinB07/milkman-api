<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\Banners\CreateBannerAction;
use App\Actions\Admin\Banners\DeleteBannerAction;
use App\Actions\Admin\Banners\ListBannersAction;
use App\Actions\Admin\Banners\ShowBannerAction;
use App\Actions\Admin\Banners\UpdateBannerAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BannerRequest;
use App\Http\Requests\Admin\ListAdminResourcesRequest;
use App\Http\Requests\Admin\UpdateBannerRequest;
use App\Http\Resources\Admin\BannerResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BannerController extends Controller
{
    public function index(
        ListAdminResourcesRequest $request,
        IdentityAuthService $auth,
        ListBannersAction $banners,
    ): AnonymousResourceCollection {
        $this->authorizeBannerManagement($request, $auth);

        return BannerResource::collection($banners->execute($request->toData()));
    }

    public function store(BannerRequest $request, IdentityAuthService $auth, CreateBannerAction $create): JsonResponse
    {
        $this->authorizeBannerManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.banner_created'),
            'data' => new BannerResource($create->execute($request->toData())),
        ], 201);
    }

    public function show(Request $request, IdentityAuthService $auth, ShowBannerAction $show, int $banner): BannerResource
    {
        $this->authorizeBannerManagement($request, $auth);

        return new BannerResource($show->execute($banner));
    }

    public function update(
        UpdateBannerRequest $request,
        IdentityAuthService $auth,
        UpdateBannerAction $update,
        int $banner,
    ): JsonResponse {
        $this->authorizeBannerManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.banner_updated'),
            'data' => new BannerResource($update->execute($banner, $request->toData())),
        ]);
    }

    public function destroy(Request $request, IdentityAuthService $auth, DeleteBannerAction $delete, int $banner): JsonResponse
    {
        $this->authorizeBannerManagement($request, $auth);
        $delete->execute($banner);

        return response()->json([
            'message' => __('catalog.banner_deleted'),
        ]);
    }

    private function authorizeBannerManagement(Request $request, IdentityAuthService $auth): void
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'admin');

        if (! $identity->can('settings.update')) {
            throw new MissingPermissionException;
        }
    }
}
