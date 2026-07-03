<?php

namespace App\Http\Controllers\Api\V1\Store;

use App\Actions\Store\Pages\ListStorePagesAction;
use App\Actions\Store\Pages\ShowStorePageAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\ListStoreResourcesRequest;
use App\Http\Resources\Store\StorePageResource;
use App\Models\Store;
use App\Services\IdentityAuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StorePageController extends Controller
{
    public function index(
        ListStoreResourcesRequest $request,
        IdentityAuthService $auth,
        ListStorePagesAction $pages,
    ): AnonymousResourceCollection {
        $this->storeIdentity($request, $auth);

        return StorePageResource::collection($pages->execute($request->toData()));
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowStorePageAction $show,
        int $page,
    ): StorePageResource {
        $this->storeIdentity($request, $auth);

        return new StorePageResource($show->execute($page));
    }

    private function storeIdentity(Request $request, IdentityAuthService $auth): Store
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'store');

        if (! $identity->can('stores.view')) {
            throw new MissingPermissionException;
        }

        return $identity;
    }
}
