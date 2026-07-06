<?php

namespace App\Http\Controllers\Api\V1\Rider;

use App\Actions\Rider\Pages\ListRiderPagesAction;
use App\Actions\Rider\Pages\ShowRiderPageAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Rider\ListRiderResourcesRequest;
use App\Http\Resources\Rider\RiderPageResource;
use App\Models\Rider;
use App\Services\IdentityAuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RiderPageController extends Controller
{
    public function index(
        ListRiderResourcesRequest $request,
        IdentityAuthService $auth,
        ListRiderPagesAction $pages,
    ): AnonymousResourceCollection {
        $this->riderIdentity($request, $auth);

        return RiderPageResource::collection($pages->execute($request->toData()));
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowRiderPageAction $show,
        int $page,
    ): RiderPageResource {
        $this->riderIdentity($request, $auth);

        return new RiderPageResource($show->execute($page));
    }

    private function riderIdentity(Request $request, IdentityAuthService $auth): Rider
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'rider');

        if (! $identity->can('orders.view')) {
            throw new MissingPermissionException;
        }

        return $identity;
    }
}
