<?php

namespace App\Http\Controllers\Api\V1\Rider;

use App\Actions\Rider\Dashboard\ShowRiderDashboardAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Resources\Rider\RiderDashboardResource;
use App\Models\Rider;
use App\Services\IdentityAuthService;
use Illuminate\Http\Request;

class RiderDashboardController extends Controller
{
    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowRiderDashboardAction $dashboard,
    ): RiderDashboardResource {
        return new RiderDashboardResource($dashboard->execute($this->riderIdentity($request, $auth)));
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
