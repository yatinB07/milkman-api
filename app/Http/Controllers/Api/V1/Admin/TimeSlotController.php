<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\TimeSlots\CreateTimeSlotAction;
use App\Actions\Admin\TimeSlots\DeleteTimeSlotAction;
use App\Actions\Admin\TimeSlots\ListTimeSlotsAction;
use App\Actions\Admin\TimeSlots\ShowTimeSlotAction;
use App\Actions\Admin\TimeSlots\UpdateTimeSlotAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListAdminResourcesRequest;
use App\Http\Requests\Admin\TimeSlotRequest;
use App\Http\Requests\Admin\UpdateTimeSlotRequest;
use App\Http\Resources\Admin\TimeSlotResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TimeSlotController extends Controller
{
    public function index(
        ListAdminResourcesRequest $request,
        IdentityAuthService $auth,
        ListTimeSlotsAction $timeSlots,
    ): AnonymousResourceCollection {
        $this->authorizeTimeSlotManagement($request, $auth);

        return TimeSlotResource::collection($timeSlots->execute($request->toData()));
    }

    public function store(
        TimeSlotRequest $request,
        IdentityAuthService $auth,
        CreateTimeSlotAction $create,
    ): JsonResponse {
        $this->authorizeTimeSlotManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.time_slot_created'),
            'data' => new TimeSlotResource($create->execute($request->toData())),
        ], 201);
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowTimeSlotAction $show,
        int $timeSlot,
    ): TimeSlotResource {
        $this->authorizeTimeSlotManagement($request, $auth);

        return new TimeSlotResource($show->execute($timeSlot));
    }

    public function update(
        UpdateTimeSlotRequest $request,
        IdentityAuthService $auth,
        UpdateTimeSlotAction $update,
        int $timeSlot,
    ): JsonResponse {
        $this->authorizeTimeSlotManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.time_slot_updated'),
            'data' => new TimeSlotResource($update->execute($timeSlot, $request->toData())),
        ]);
    }

    public function destroy(
        Request $request,
        IdentityAuthService $auth,
        DeleteTimeSlotAction $delete,
        int $timeSlot,
    ): JsonResponse {
        $this->authorizeTimeSlotManagement($request, $auth);
        $delete->execute($timeSlot);

        return response()->json([
            'message' => __('catalog.time_slot_deleted'),
        ]);
    }

    private function authorizeTimeSlotManagement(Request $request, IdentityAuthService $auth): void
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'admin');

        if (! $identity->can('stores.manage')) {
            throw new MissingPermissionException;
        }
    }
}
