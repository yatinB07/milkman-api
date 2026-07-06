<?php

namespace App\Http\Controllers\Api\V1\Store;

use App\Actions\Store\Account\DeactivateStoreAccountAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreAccountController extends Controller
{
    public function destroy(
        Request $request,
        IdentityAuthService $auth,
        DeactivateStoreAccountAction $deactivate,
    ): JsonResponse {
        $deactivate->execute($this->storeIdentity($request, $auth));

        return response()->json([
            'message' => __('catalog.store_account_deactivated'),
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
