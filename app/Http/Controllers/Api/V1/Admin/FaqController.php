<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\Faqs\CreateFaqAction;
use App\Actions\Admin\Faqs\DeleteFaqAction;
use App\Actions\Admin\Faqs\ListFaqsAction;
use App\Actions\Admin\Faqs\ShowFaqAction;
use App\Actions\Admin\Faqs\UpdateFaqAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FaqRequest;
use App\Http\Requests\Admin\ListAdminResourcesRequest;
use App\Http\Requests\Admin\UpdateFaqRequest;
use App\Http\Resources\Admin\FaqResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FaqController extends Controller
{
    public function index(
        ListAdminResourcesRequest $request,
        IdentityAuthService $auth,
        ListFaqsAction $faqs,
    ): AnonymousResourceCollection {
        $this->authorizeFaqManagement($request, $auth);

        return FaqResource::collection($faqs->execute($request->toData()));
    }

    public function store(
        FaqRequest $request,
        IdentityAuthService $auth,
        CreateFaqAction $create,
    ): JsonResponse {
        $this->authorizeFaqManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.faq_created'),
            'data' => new FaqResource($create->execute($request->toData())),
        ], 201);
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowFaqAction $show,
        int $faq,
    ): FaqResource {
        $this->authorizeFaqManagement($request, $auth);

        return new FaqResource($show->execute($faq));
    }

    public function update(
        UpdateFaqRequest $request,
        IdentityAuthService $auth,
        UpdateFaqAction $update,
        int $faq,
    ): JsonResponse {
        $this->authorizeFaqManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.faq_updated'),
            'data' => new FaqResource($update->execute($faq, $request->toData())),
        ]);
    }

    public function destroy(
        Request $request,
        IdentityAuthService $auth,
        DeleteFaqAction $delete,
        int $faq,
    ): JsonResponse {
        $this->authorizeFaqManagement($request, $auth);
        $delete->execute($faq);

        return response()->json([
            'message' => __('catalog.faq_deleted'),
        ]);
    }

    private function authorizeFaqManagement(Request $request, IdentityAuthService $auth): void
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'admin');

        if (! $identity->can('stores.manage')) {
            throw new MissingPermissionException;
        }
    }
}
