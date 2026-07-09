<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'company_name' => ['nullable', 'string', 'max:255'],
        ]);

        $company = Company::firstOrCreate(
            ['name' => $data['company_name'] ?? 'NexaERP Workspace'],
            ['email' => $data['email'], 'currency' => 'USD']
        );

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'company_id' => $company->id,
            'locale' => 'en',
        ]);
        $user->assignRole('Employee');

        return response()->json($this->tokenResponse($user), 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Invalid email or password.'], 422);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'Your account is inactive.'], 403);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        return response()->json($this->tokenResponse($user));
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load(['company', 'branch', 'roles', 'permissions']),
            'permissions' => $request->user()->getAllPermissions()->pluck('name')->values(),
            'roles' => $request->user()->roles->pluck('name')->values(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'avatar_url' => ['nullable', 'url', 'max:500'],
            'locale' => ['nullable', 'in:en,ar'],
            'theme' => ['nullable', 'in:light,dark'],
        ]);

        $request->user()->update($data);

        return response()->json($request->user()->fresh(['company', 'branch', 'roles']));
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (! Hash::check($data['current_password'], $request->user()->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 422);
        }

        $request->user()->update(['password' => $data['password']]);
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Password changed. Please sign in again.']);
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        return response()->json(['message' => 'Password reset delivery is configured for future mail integration.']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        return response()->json(['message' => 'Reset token validation endpoint is ready for mail integration.']);
    }

    private function tokenResponse(User $user): array
    {
        return [
            'token' => $user->createToken('nexaerp-web')->plainTextToken,
            'user' => $user->load(['company', 'branch', 'roles']),
            'permissions' => $user->getAllPermissions()->pluck('name')->values(),
            'roles' => $user->roles->pluck('name')->values(),
        ];
    }
}