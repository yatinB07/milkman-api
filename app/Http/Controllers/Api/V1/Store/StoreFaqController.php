<?php

namespace App\Http\Controllers\Api\V1\Store;

use App\Actions\Store\Faqs\CreateStoreFaqAction;
use App\Actions\Store\Faqs\DeleteStoreFaqAction;
use App\Actions\Store\Faqs\ListStoreFaqsAction;
use App\Actions\Store\Faqs\ShowStoreFaqAction;
use App\Actions\Store\Faqs\UpdateStoreFaqAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\ListStoreResourcesRequest;
use App\Http\Requests\Store\StoreFaqRequest;
use App\Http\Requests\Store\UpdateStoreFaqRequest;
use App\Http\Resources\Store\StoreFaqResource;
use App\Models\Store;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StoreFaqController extends Controller
{
    public function index(
        ListStoreResourcesRequest $request,
        IdentityAuthService $auth,
        ListStoreFaqsAction $faqs,
    ): AnonymousResourceCollection {
        return StoreFaqResource::collection($faqs->execute($this->storeIdentity($request, $auth), $request->toData()));
    }

    public function store(
        StoreFaqRequest $request,
        IdentityAuthService $auth,
        CreateStoreFaqAction $create,
    ): JsonResponse {
        return response()->json([
            'message' => __('catalog.faq_created'),
            'data' => new StoreFaqResource($create->execute($this->storeIdentity($request, $auth), $request->toData())),
        ], 201);
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowStoreFaqAction $show,
        int $faq,
    ): StoreFaqResource {
        return new StoreFaqResource($show->execute($this->storeIdentity($request, $auth), $faq));
    }

    public function update(
        UpdateStoreFaqRequest $request,
        IdentityAuthService $auth,
        UpdateStoreFaqAction $update,
        int $faq,
    ): JsonResponse {
        return response()->json([
            'message' => __('catalog.faq_updated'),
            'data' => new StoreFaqResource($update->execute($this->storeIdentity($request, $auth), $faq, $request->toData())),
        ]);
    }

    public function destroy(
        Request $request,
        IdentityAuthService $auth,
        DeleteStoreFaqAction $delete,
        int $faq,
    ): JsonResponse {
        $delete->execute($this->storeIdentity($request, $auth), $faq);

        return response()->json([
            'message' => __('catalog.faq_deleted'),
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
