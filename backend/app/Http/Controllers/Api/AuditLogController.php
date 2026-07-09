<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::query()->where('company_id', $request->user()->company_id)->with('user');

        foreach (['module', 'action', 'user_id'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->date('to'));
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function show(Request $request, AuditLog $auditLog)
    {
        abort_unless($auditLog->company_id === $request->user()->company_id, 403);

        return response()->json($auditLog->load('user'));
    }
}