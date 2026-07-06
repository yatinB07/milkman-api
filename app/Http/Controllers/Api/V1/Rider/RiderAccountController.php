<?php

namespace App\Http\Controllers\Api\V1\Rider;

use App\Actions\Rider\Account\DeactivateRiderAccountAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Models\Rider;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RiderAccountController extends Controller
{
    public function destroy(
        Request $request,
        IdentityAuthService $auth,
        DeactivateRiderAccountAction $deactivate,
    ): JsonResponse {
        $deactivate->execute($this->riderIdentity($request, $auth));

        return response()->json([
            'message' => __('catalog.rider_account_deactivated'),
        ]);
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
