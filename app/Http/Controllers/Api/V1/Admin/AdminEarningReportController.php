<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\Reports\ListAdminEarningReportsAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListAdminResourcesRequest;
use App\Http\Resources\Admin\AdminEarningReportResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdminEarningReportController extends Controller
{
    public function index(
        ListAdminResourcesRequest $request,
        IdentityAuthService $auth,
        ListAdminEarningReportsAction $reports,
    ): AnonymousResourceCollection {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'admin');

        if (! $identity->can('reports.view')) {
            throw new MissingPermissionException;
        }

        return AdminEarningReportResource::collection($reports->execute($request->toData()));
    }
}
