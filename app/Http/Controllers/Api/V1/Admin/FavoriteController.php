<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\Favorites\CreateFavoriteAction;
use App\Actions\Admin\Favorites\DeleteFavoriteAction;
use App\Actions\Admin\Favorites\ListFavoritesAction;
use App\Actions\Admin\Favorites\ShowFavoriteAction;
use App\Actions\Admin\Favorites\UpdateFavoriteAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FavoriteRequest;
use App\Http\Requests\Admin\ListAdminResourcesRequest;
use App\Http\Requests\Admin\UpdateFavoriteRequest;
use App\Http\Resources\Admin\FavoriteResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FavoriteController extends Controller
{
    public function index(
        ListAdminResourcesRequest $request,
        IdentityAuthService $auth,
        ListFavoritesAction $favorites,
    ): AnonymousResourceCollection {
        $this->authorizeUserManagement($request, $auth);

        return FavoriteResource::collection($favorites->execute($request->toData()));
    }

    public function store(FavoriteRequest $request, IdentityAuthService $auth, CreateFavoriteAction $create): JsonResponse
    {
        $this->authorizeUserManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.favorite_created'),
            'data' => new FavoriteResource($create->execute($request->toData())),
        ], 201);
    }

    public function show(Request $request, IdentityAuthService $auth, ShowFavoriteAction $show, int $favorite): FavoriteResource
    {
        $this->authorizeUserManagement($request, $auth);

        return new FavoriteResource($show->execute($favorite));
    }

    public function update(
        UpdateFavoriteRequest $request,
        IdentityAuthService $auth,
        UpdateFavoriteAction $update,
        int $favorite,
    ): JsonResponse {
        $this->authorizeUserManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.favorite_updated'),
            'data' => new FavoriteResource($update->execute($favorite, $request->toData())),
        ]);
    }

    public function destroy(Request $request, IdentityAuthService $auth, DeleteFavoriteAction $delete, int $favorite): JsonResponse
    {
        $this->authorizeUserManagement($request, $auth);
        $delete->execute($favorite);

        return response()->json([
            'message' => __('catalog.favorite_deleted'),
        ]);
    }

    private function authorizeUserManagement(Request $request, IdentityAuthService $auth): void
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'admin');

        if (! $identity->can('users.manage')) {
            throw new MissingPermissionException;
        }
    }
}
