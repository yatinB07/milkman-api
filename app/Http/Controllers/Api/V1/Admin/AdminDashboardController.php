<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\Dashboard\ShowAdminDashboardAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminDashboardResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function show(Request $request, IdentityAuthService $auth, ShowAdminDashboardAction $dashboard): AdminDashboardResource
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'admin');

        if (! $identity->can('reports.view')) {
            throw new MissingPermissionException;
        }

        return new AdminDashboardResource($dashboard->execute());
    }
}
