<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\Pages\CreatePageAction;
use App\Actions\Admin\Pages\DeletePageAction;
use App\Actions\Admin\Pages\ListPagesAction;
use App\Actions\Admin\Pages\ShowPageAction;
use App\Actions\Admin\Pages\UpdatePageAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListAdminResourcesRequest;
use App\Http\Requests\Admin\PageRequest;
use App\Http\Requests\Admin\UpdatePageRequest;
use App\Http\Resources\Admin\PageResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PageController extends Controller
{
    public function index(
        ListAdminResourcesRequest $request,
        IdentityAuthService $auth,
        ListPagesAction $pages,
    ): AnonymousResourceCollection {
        $this->authorizePageManagement($request, $auth);

        return PageResource::collection($pages->execute($request->toData()));
    }

    public function store(
        PageRequest $request,
        IdentityAuthService $auth,
        CreatePageAction $create,
    ): JsonResponse {
        $this->authorizePageManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.page_created'),
            'data' => new PageResource($create->execute($request->toData())),
        ], 201);
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowPageAction $show,
        int $page,
    ): PageResource {
        $this->authorizePageManagement($request, $auth);

        return new PageResource($show->execute($page));
    }

    public function update(
        UpdatePageRequest $request,
        IdentityAuthService $auth,
        UpdatePageAction $update,
        int $page,
    ): JsonResponse {
        $this->authorizePageManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.page_updated'),
            'data' => new PageResource($update->execute($page, $request->toData())),
        ]);
    }

    public function destroy(
        Request $request,
        IdentityAuthService $auth,
        DeletePageAction $delete,
        int $page,
    ): JsonResponse {
        $this->authorizePageManagement($request, $auth);
        $delete->execute($page);

        return response()->json([
            'message' => __('catalog.page_deleted'),
        ]);
    }

    private function authorizePageManagement(Request $request, IdentityAuthService $auth): void
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'admin');

        if (! $identity->can('settings.update')) {
            throw new MissingPermissionException;
        }
    }
}
