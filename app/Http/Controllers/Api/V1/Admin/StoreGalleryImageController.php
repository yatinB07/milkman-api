<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\StoreGalleryImages\CreateStoreGalleryImageAction;
use App\Actions\Admin\StoreGalleryImages\DeleteStoreGalleryImageAction;
use App\Actions\Admin\StoreGalleryImages\ListStoreGalleryImagesAction;
use App\Actions\Admin\StoreGalleryImages\ShowStoreGalleryImageAction;
use App\Actions\Admin\StoreGalleryImages\UpdateStoreGalleryImageAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListAdminResourcesRequest;
use App\Http\Requests\Admin\StoreGalleryImageRequest;
use App\Http\Requests\Admin\UpdateStoreGalleryImageRequest;
use App\Http\Resources\Admin\StoreGalleryImageResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StoreGalleryImageController extends Controller
{
    public function index(
        ListAdminResourcesRequest $request,
        IdentityAuthService $auth,
        ListStoreGalleryImagesAction $images,
    ): AnonymousResourceCollection {
        $this->authorizeStoreGalleryImageManagement($request, $auth);

        return StoreGalleryImageResource::collection($images->execute($request->toData()));
    }

    public function store(
        StoreGalleryImageRequest $request,
        IdentityAuthService $auth,
        CreateStoreGalleryImageAction $create,
    ): JsonResponse {
        $this->authorizeStoreGalleryImageManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.store_gallery_image_created'),
            'data' => new StoreGalleryImageResource($create->execute($request->toData())),
        ], 201);
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowStoreGalleryImageAction $show,
        int $storeGalleryImage,
    ): StoreGalleryImageResource {
        $this->authorizeStoreGalleryImageManagement($request, $auth);

        return new StoreGalleryImageResource($show->execute($storeGalleryImage));
    }

    public function update(
        UpdateStoreGalleryImageRequest $request,
        IdentityAuthService $auth,
        UpdateStoreGalleryImageAction $update,
        int $storeGalleryImage,
    ): JsonResponse {
        $this->authorizeStoreGalleryImageManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.store_gallery_image_updated'),
            'data' => new StoreGalleryImageResource($update->execute($storeGalleryImage, $request->toData())),
        ]);
    }

    public function destroy(
        Request $request,
        IdentityAuthService $auth,
        DeleteStoreGalleryImageAction $delete,
        int $storeGalleryImage,
    ): JsonResponse {
        $this->authorizeStoreGalleryImageManagement($request, $auth);
        $delete->execute($storeGalleryImage);

        return response()->json([
            'message' => __('catalog.store_gallery_image_deleted'),
        ]);
    }

    private function authorizeStoreGalleryImageManagement(Request $request, IdentityAuthService $auth): void
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'admin');

        if (! $identity->can('stores.manage')) {
            throw new MissingPermissionException;
        }
    }
}
