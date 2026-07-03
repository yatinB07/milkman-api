<?php

namespace App\Http\Controllers\Api\V1\Store;

use App\Actions\Store\GalleryImages\CreateStoreGalleryImageAction;
use App\Actions\Store\GalleryImages\DeleteStoreGalleryImageAction;
use App\Actions\Store\GalleryImages\ListStoreGalleryImagesAction;
use App\Actions\Store\GalleryImages\ShowStoreGalleryImageAction;
use App\Actions\Store\GalleryImages\UpdateStoreGalleryImageAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\ListStoreResourcesRequest;
use App\Http\Requests\Store\StoreGalleryImageRequest;
use App\Http\Requests\Store\UpdateStoreGalleryImageRequest;
use App\Http\Resources\Store\StoreGalleryImageResource;
use App\Models\Store;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StoreGalleryImageController extends Controller
{
    public function index(
        ListStoreResourcesRequest $request,
        IdentityAuthService $auth,
        ListStoreGalleryImagesAction $images,
    ): AnonymousResourceCollection {
        return StoreGalleryImageResource::collection($images->execute($this->storeIdentity($request, $auth), $request->toData()));
    }

    public function store(
        StoreGalleryImageRequest $request,
        IdentityAuthService $auth,
        CreateStoreGalleryImageAction $create,
    ): JsonResponse {
        return response()->json([
            'message' => __('catalog.store_gallery_image_created'),
            'data' => new StoreGalleryImageResource($create->execute($this->storeIdentity($request, $auth), $request->toData())),
        ], 201);
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowStoreGalleryImageAction $show,
        int $galleryImage,
    ): StoreGalleryImageResource {
        return new StoreGalleryImageResource($show->execute($this->storeIdentity($request, $auth), $galleryImage));
    }

    public function update(
        UpdateStoreGalleryImageRequest $request,
        IdentityAuthService $auth,
        UpdateStoreGalleryImageAction $update,
        int $galleryImage,
    ): JsonResponse {
        return response()->json([
            'message' => __('catalog.store_gallery_image_updated'),
            'data' => new StoreGalleryImageResource($update->execute($this->storeIdentity($request, $auth), $galleryImage, $request->toData())),
        ]);
    }

    public function destroy(
        Request $request,
        IdentityAuthService $auth,
        DeleteStoreGalleryImageAction $delete,
        int $galleryImage,
    ): JsonResponse {
        $delete->execute($this->storeIdentity($request, $auth), $galleryImage);

        return response()->json([
            'message' => __('catalog.store_gallery_image_deleted'),
        ]);
    }

    private function storeIdentity(Request $request, IdentityAuthService $auth): Store
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'store');

        if (! $identity->can('stores.update')) {
            throw new MissingPermissionException;
        }

        return $identity;
    }
}
