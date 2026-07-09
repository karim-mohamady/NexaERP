<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AiCopilotService;
use Illuminate\Http\Request;

class AiCopilotController extends Controller
{
    public function chat(Request $request, AiCopilotService $copilot)
    {
        $data = $request->validate(['message' => ['nullable', 'string', 'max:2000']]);

        return response()->json($copilot->answer($request->user()->company_id, $data['message'] ?? ''));
    }

    public function salesAnalysis(Request $request, AiCopilotService $copilot) { return response()->json($copilot->answer($request->user()->company_id, 'sales decrease')); }
    public function inventoryRisk(Request $request, AiCopilotService $copilot) { return response()->json($copilot->answer($request->user()->company_id, 'inventory stock risk')); }
    public function expenseAnomalies(Request $request, AiCopilotService $copilot) { return response()->json($copilot->answer($request->user()->company_id, 'expense anomalies')); }
    public function customerSegments(Request $request, AiCopilotService $copilot) { return response()->json($copilot->answer($request->user()->company_id, 'customer segments')); }
    public function forecast(Request $request, AiCopilotService $copilot) { return response()->json($copilot->answer($request->user()->company_id, 'forecast this month')); }
}