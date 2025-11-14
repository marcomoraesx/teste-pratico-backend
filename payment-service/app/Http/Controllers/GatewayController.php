<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActivateOrDeactivateGatewayRequest;
use App\Http\Requests\ChangeGatewayPriorityRequest;
use App\Services\GatewayService;
use Illuminate\Http\JsonResponse;

class GatewayController extends Controller
{
    private GatewayService $gateway_service;

    function __construct(GatewayService $gateway_service)
    {
        $this->gateway_service = $gateway_service;
    }

    function change_priority(string $gateway_id, ChangeGatewayPriorityRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->gateway_service->change_priority(intval($gateway_id), $data['priority']);
        return response()->json([
            'message' => 'Updated successful',
        ], 200);
    }

    function activate_or_deactivate(string $gateway_id, ActivateOrDeactivateGatewayRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->gateway_service->activate_or_deactivate(intval($gateway_id), $data['activate']);
        return response()->json([
            'message' => 'Updated successful',
        ], 200);
    }
}
