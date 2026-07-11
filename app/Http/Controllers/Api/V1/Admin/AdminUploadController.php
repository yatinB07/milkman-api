<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\Uploads\StoreAdminUploadAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminUploadRequest;
use App\Http\Resources\Admin\AdminUploadResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;

class AdminUploadController extends Controller
{
    public function __invoke(
        AdminUploadRequest $request,
        IdentityAuthService $auth,
        StoreAdminUploadAction $storeUpload,
    ): JsonResponse {
        $auth->assertTokenMatchesIdentityType($request->user(), 'admin');

        $data = $request->validated();

        return response()->json([
            'message' => __('catalog.file_uploaded'),
            'data' => new AdminUploadResource($storeUpload->execute($data['file'], $data['directory'])),
        ], 201);
    }
}
