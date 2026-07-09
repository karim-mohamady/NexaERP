<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Setting;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = Notification::where('company_id', $request->user()->company_id)
            ->where(fn ($q) => $q->whereNull('user_id')->orWhere('user_id', $request->user()->id));

        return response()->json([
            'unread' => (clone $query)->whereNull('read_at')->count(),
            'data' => $query->latest()->limit(20)->get(),
        ]);
    }

    public function markRead(Request $request, Notification $notification)
    {
        abort_unless($notification->company_id === $request->user()->company_id, 403);
        $notification->update(['read_at' => now()]);

        return response()->json($notification);
    }

    public function markAllRead(Request $request)
    {
        Notification::where('company_id', $request->user()->company_id)
            ->where(fn ($q) => $q->whereNull('user_id')->orWhere('user_id', $request->user()->id))
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read']);
    }

    public function preferences(Request $request)
    {
        $setting = Setting::firstOrCreate(
            ['company_id' => $request->user()->company_id, 'group' => 'notifications', 'key' => 'preferences'],
            ['value' => ['approval' => true, 'inventory' => true, 'ai' => true]]
        );

        return response()->json($setting);
    }
}