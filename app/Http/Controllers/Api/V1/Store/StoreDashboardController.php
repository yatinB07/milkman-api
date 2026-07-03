<?php

namespace App\Http\Controllers\Api\V1\Store;

use App\Actions\Store\Dashboard\ShowStoreDashboardAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Store\StoreDashboardResource;
use App\Models\Store;
use App\Services\IdentityAuthService;
use Illuminate\Http\Request;

class StoreDashboardController extends Controller
{
    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowStoreDashboardAction $dashboard,
    ): StoreDashboardResource {
        return new StoreDashboardResource($dashboard->execute($this->store($request, $auth)));
    }

    private function store(Request $request, IdentityAuthService $auth): Store
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'store');

        return $identity;
    }
}
