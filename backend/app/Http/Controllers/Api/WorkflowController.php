<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class WorkflowController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(Workflow::with('steps')->where('company_id', $request->user()->company_id)->latest()->paginate(15));
    }

    public function store(Request $request, AuditLogger $audit)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'module' => ['required', 'string', 'max:100'],
            'trigger_type' => ['nullable', 'string', 'max:100'],
            'amount_threshold' => ['nullable', 'numeric'],
            'required_role' => ['nullable', 'string', 'max:100'],
            'steps' => ['nullable', 'array'],
        ]);

        $workflow = Workflow::create([
            ...collect($data)->except('steps')->toArray(),
            'company_id' => $request->user()->company_id,
            'branch_id' => $request->user()->branch_id,
            'is_active' => true,
        ]);

        foreach (($data['steps'] ?? [['name' => 'Manager approval', 'required_role' => $data['required_role'] ?? 'Manager']]) as $index => $step) {
            WorkflowStep::create([
                'workflow_id' => $workflow->id,
                'name' => $step['name'] ?? 'Approval step',
                'required_role' => $step['required_role'] ?? 'Manager',
                'approval_order' => $step['approval_order'] ?? ($index + 1),
                'is_final' => $index === count($data['steps'] ?? [1]) - 1,
            ]);
        }

        $audit->log($request, 'workflows', 'create', $workflow->id, [], $workflow->toArray());

        return response()->json($workflow->load('steps'), 201);
    }

    public function update(Request $request, Workflow $workflow, AuditLogger $audit)
    {
        $old = $workflow->toArray();
        $workflow->update($request->only(['name', 'module', 'trigger_type', 'amount_threshold', 'required_role', 'status', 'is_active']));
        $audit->log($request, 'workflows', 'update', $workflow->id, $old, $workflow->fresh()->toArray());

        return response()->json($workflow->fresh('steps'));
    }

    public function destroy(Request $request, Workflow $workflow, AuditLogger $audit)
    {
        $old = $workflow->toArray();
        $workflow->delete();
        $audit->log($request, 'workflows', 'delete', $workflow->id, $old, []);

        return response()->json(['message' => 'Workflow deleted']);
    }
}