<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Approval;
use App\Models\Notification;
use App\Models\Workflow;
use App\Models\WorkflowRequest;
use App\Models\WorkflowRequestStep;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function inbox(Request $request)
    {
        $roles = $request->user()->roles->pluck('name')->all();

        return response()->json(WorkflowRequest::query()
            ->with(['steps'])
            ->where('company_id', $request->user()->company_id)
            ->where('status', 'pending')
            ->whereHas('steps', fn ($query) => $query->where('status', 'pending')->whereIn('required_role', $roles))
            ->latest()
            ->paginate(15));
    }

    public function myRequests(Request $request)
    {
        return response()->json(WorkflowRequest::with('steps')->where('requested_by', $request->user()->id)->latest()->paginate(15));
    }

    public function show(Request $request, WorkflowRequest $workflowRequest)
    {
        abort_unless($workflowRequest->company_id === $request->user()->company_id, 403);

        return response()->json($workflowRequest->load('steps'));
    }

    public function submit(Request $request, AuditLogger $audit)
    {
        $data = $request->validate([
            'module' => ['required', 'string'],
            'record_type' => ['nullable', 'string'],
            'record_id' => ['nullable', 'integer'],
            'amount' => ['nullable', 'numeric'],
            'comments' => ['nullable', 'string'],
        ]);

        $workflow = Workflow::with('steps')
            ->where('company_id', $request->user()->company_id)
            ->where('module', $data['module'])
            ->where('is_active', true)
            ->where('amount_threshold', '<=', $data['amount'] ?? 0)
            ->orderByDesc('amount_threshold')
            ->first();

        $workflow ??= Workflow::with('steps')->where('company_id', $request->user()->company_id)->where('is_active', true)->first();

        $approvalRequest = WorkflowRequest::create([
            'company_id' => $request->user()->company_id,
            'branch_id' => $request->user()->branch_id,
            'workflow_id' => $workflow?->id,
            'requested_by' => $request->user()->id,
            'module' => $data['module'],
            'record_type' => $data['record_type'] ?? null,
            'record_id' => $data['record_id'] ?? null,
            'amount' => $data['amount'] ?? 0,
            'comments' => $data['comments'] ?? null,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $steps = $workflow?->steps;
        if (! $steps || $steps->isEmpty()) {
            $steps = collect([(object) ['id' => null, 'required_role' => 'Manager', 'approval_order' => 1]]);
        }

        foreach ($steps as $step) {
            WorkflowRequestStep::create([
                'workflow_request_id' => $approvalRequest->id,
                'workflow_step_id' => $step->id ?? null,
                'required_role' => $step->required_role,
                'approval_order' => $step->approval_order,
                'status' => 'pending',
            ]);
        }

        Notification::create([
            'company_id' => $request->user()->company_id,
            'title' => 'Approval request submitted',
            'body' => "{$data['module']} requires approval.",
            'type' => 'approval request',
            'priority' => 'high',
            'action_url' => '/app/approvals',
            'data' => ['workflow_request_id' => $approvalRequest->id],
        ]);

        $audit->log($request, 'approvals', 'submit', $approvalRequest->id, [], $approvalRequest->toArray());

        return response()->json($approvalRequest->load('steps'), 201);
    }

    public function approve(Request $request, WorkflowRequest $workflowRequest, AuditLogger $audit)
    {
        return $this->act($request, $workflowRequest, 'approved', $audit);
    }

    public function reject(Request $request, WorkflowRequest $workflowRequest, AuditLogger $audit)
    {
        return $this->act($request, $workflowRequest, 'rejected', $audit);
    }

    public function returnForRevision(Request $request, WorkflowRequest $workflowRequest, AuditLogger $audit)
    {
        return $this->act($request, $workflowRequest, 'returned', $audit);
    }

    public function cancel(Request $request, WorkflowRequest $workflowRequest, AuditLogger $audit)
    {
        return $this->act($request, $workflowRequest, 'cancelled', $audit);
    }

    private function act(Request $request, WorkflowRequest $workflowRequest, string $status, AuditLogger $audit)
    {
        abort_unless($workflowRequest->company_id === $request->user()->company_id, 403);
        $data = $request->validate(['comment' => ['nullable', 'string']]);
        $old = $workflowRequest->toArray();

        $workflowRequest->update([
            'status' => $status,
            'completed_at' => in_array($status, ['approved', 'rejected', 'cancelled'], true) ? now() : null,
        ]);

        $workflowRequest->steps()->where('status', 'pending')->orderBy('approval_order')->limit(1)->update([
            'status' => $status,
            'acted_by' => $request->user()->id,
            'acted_at' => now(),
            'comments' => $data['comment'] ?? null,
        ]);

        Approval::create([
            'company_id' => $request->user()->company_id,
            'branch_id' => $request->user()->branch_id,
            'workflow_request_id' => $workflowRequest->id,
            'module' => $workflowRequest->module,
            'record_type' => $workflowRequest->record_type,
            'record_id' => $workflowRequest->record_id,
            'status' => $status,
            'comment' => $data['comment'] ?? null,
            'approved_by' => $status === 'approved' ? $request->user()->id : null,
            'rejected_by' => $status === 'rejected' ? $request->user()->id : null,
            'acted_at' => now(),
        ]);

        $audit->log($request, 'approvals', $status, $workflowRequest->id, $old, $workflowRequest->fresh()->toArray());

        return response()->json($workflowRequest->fresh('steps'));
    }
}