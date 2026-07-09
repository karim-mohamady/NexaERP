<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(Setting::where('company_id', $request->user()->company_id)->get()->groupBy('group'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'group' => ['required', 'string', 'max:100'],
            'key' => ['required', 'string', 'max:100'],
            'value' => ['nullable'],
        ]);

        $setting = Setting::updateOrCreate(
            ['company_id' => $request->user()->company_id, 'group' => $data['group'], 'key' => $data['key']],
            ['value' => $data['value'] ?? null]
        );

        return response()->json($setting);
    }
}