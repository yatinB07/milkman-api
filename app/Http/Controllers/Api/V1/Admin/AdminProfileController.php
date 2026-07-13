<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\Profile\ShowAdminProfileAction;
use App\Actions\Admin\Profile\UpdateAdminPasswordAction;
use App\Actions\Admin\Profile\UpdateAdminProfileAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminPasswordRequest;
use App\Http\Requests\Admin\AdminProfileRequest;
use App\Http\Resources\Admin\AdminProfileResource;
use App\Models\Admin;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminProfileController extends Controller
{
    public function show(Request $request, IdentityAuthService $auth, ShowAdminProfileAction $show): AdminProfileResource
    {
        return new AdminProfileResource($show->execute($this->admin($request, $auth)));
    }

    public function update(
        AdminProfileRequest $request,
        IdentityAuthService $auth,
        UpdateAdminProfileAction $update,
    ): JsonResponse {
        return response()->json([
            'message' => __('auth.admin_profile_updated'),
            'data' => new AdminProfileResource($update->execute($this->admin($request, $auth), $request->toData())),
        ]);
    }

    public function password(
        AdminPasswordRequest $request,
        IdentityAuthService $auth,
        UpdateAdminPasswordAction $update,
    ): JsonResponse {
        return response()->json([
            'message' => __('auth.admin_password_updated'),
            'data' => new AdminProfileResource($update->execute($this->admin($request, $auth), $request->toData())),
        ]);
    }

    private function admin(Request $request, IdentityAuthService $auth): Admin
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'admin');

        return $identity;
    }
}
