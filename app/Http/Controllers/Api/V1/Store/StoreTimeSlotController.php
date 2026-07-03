<?php

namespace App\Http\Controllers\Api\V1\Store;

use App\Actions\Store\TimeSlots\CreateStoreTimeSlotAction;
use App\Actions\Store\TimeSlots\DeleteStoreTimeSlotAction;
use App\Actions\Store\TimeSlots\ListStoreTimeSlotsAction;
use App\Actions\Store\TimeSlots\ShowStoreTimeSlotAction;
use App\Actions\Store\TimeSlots\UpdateStoreTimeSlotAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\ListStoreResourcesRequest;
use App\Http\Requests\Store\StoreTimeSlotRequest;
use App\Http\Requests\Store\UpdateStoreTimeSlotRequest;
use App\Http\Resources\Store\StoreTimeSlotResource;
use App\Models\Store;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StoreTimeSlotController extends Controller
{
    public function index(
        ListStoreResourcesRequest $request,
        IdentityAuthService $auth,
        ListStoreTimeSlotsAction $timeSlots,
    ): AnonymousResourceCollection {
        return StoreTimeSlotResource::collection($timeSlots->execute($this->storeIdentity($request, $auth), $request->toData()));
    }

    public function store(
        StoreTimeSlotRequest $request,
        IdentityAuthService $auth,
        CreateStoreTimeSlotAction $create,
    ): JsonResponse {
        return response()->json([
            'message' => __('catalog.time_slot_created'),
            'data' => new StoreTimeSlotResource($create->execute($this->storeIdentity($request, $auth), $request->toData())),
        ], 201);
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowStoreTimeSlotAction $show,
        int $timeSlot,
    ): StoreTimeSlotResource {
        return new StoreTimeSlotResource($show->execute($this->storeIdentity($request, $auth), $timeSlot));
    }

    public function update(
        UpdateStoreTimeSlotRequest $request,
        IdentityAuthService $auth,
        UpdateStoreTimeSlotAction $update,
        int $timeSlot,
    ): JsonResponse {
        return response()->json([
            'message' => __('catalog.time_slot_updated'),
            'data' => new StoreTimeSlotResource($update->execute($this->storeIdentity($request, $auth), $timeSlot, $request->toData())),
        ]);
    }

    public function destroy(
        Request $request,
        IdentityAuthService $auth,
        DeleteStoreTimeSlotAction $delete,
        int $timeSlot,
    ): JsonResponse {
        $delete->execute($this->storeIdentity($request, $auth), $timeSlot);

        return response()->json([
            'message' => __('catalog.time_slot_deleted'),
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
