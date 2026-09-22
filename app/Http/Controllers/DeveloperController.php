<?php

namespace App\Http\Controllers;

use App\Models\Developer;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class DeveloperController extends Controller
{
    public function showRegister()
    {
        return view('developer.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:developers',
            'password' => ['required', 'confirmed', Password::min(8)],
            'company' => 'nullable|string|max:200',
            'website' => 'nullable|url|max:500',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $developer = Developer::create($validated);

        session([
            'developer_id' => $developer->id,
            'developer_email' => $developer->email,
            'developer_name' => $developer->name,
            'login_via' => 'developer',
        ]);

        return redirect()->route('developer.dashboard');
    }

    public function showLogin()
    {
        return view('developer.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $developer = Developer::where('email', $request->email)->first();

        if (!$developer || !Hash::check($request->password, $developer->password)) {
            return back()->withErrors(['email' => 'Invalid credentials.']);
        }

        session([
            'developer_id' => $developer->id,
            'developer_email' => $developer->email,
            'developer_name' => $developer->name,
            'login_via' => 'developer',
        ]);

        return redirect()->route('developer.dashboard');
    }

    public function logout()
    {
        session()->forget(['developer_id', 'developer_email', 'developer_name', 'login_via']);
        return redirect()->route('developer.login');
    }

    public function dashboard()
    {
        $developerId = session('developer_id');
        $apps = Module::where('developer_id', $developerId)
            ->orderByDesc('created_at')
            ->get();

        return view('developer.dashboard', compact('apps'));
    }

    public function showSubmit()
    {
        return view('developer.submit');
    }

    public function profile()
    {
        $developer = Developer::find(session('developer_id'));
        $appCount = Module::where('developer_id', $developer->id)->count();
        $liveApps = Module::where('developer_id', $developer->id)->where('is_active', true)->count();
        $totalInstalls = Module::where('developer_id', $developer->id)->sum('install_count');
        return view('developer.profile', compact('developer', 'appCount', 'liveApps', 'totalInstalls'));
    }

    public function updateProfile(Request $request)
    {
        $developer = Developer::find(session('developer_id'));

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'company' => 'nullable|string|max:200',
            'website' => 'nullable|url|max:500',
        ]);

        $developer->update($validated);
        session('developer_name', $developer->name);

        return back()->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request)
    {
        $developer = Developer::find(session('developer_id'));

        $validated = $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if (!Hash::check($validated['current_password'], $developer->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $developer->update(['password' => Hash::make($validated['password'])]);

        return back()->with('success', 'Password updated.');
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:100',
            'description' => 'required|string|max:500',
            'module_url' => 'required|url|max:500',
            'test_mode_url' => 'nullable|url|max:500',
            'category' => 'nullable|string|max:100',
            'icon' => 'nullable|string|max:100',
            'icon_file' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'screenshots' => 'nullable|array|max:5',
            'screenshots.*' => 'url|max:500',
            'required_bridge_apis' => 'nullable|array',
            'required_bridge_apis.*' => 'string|max:100',
        ]);

        $developerId = session('developer_id');
        $developer = Developer::find($developerId);

        $name = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $validated['display_name']), '_'));

        if (Module::where('name', $name)->exists()) {
            $name = $name . '_' . substr(uniqid(), -4);
        }

        $iconPath = null;
        if ($request->hasFile('icon_file')) {
            $iconPath = $request->file('icon_file')->store('store-icons', 'public');
        }

        $module = Module::create([
            'name' => $name,
            'display_name' => $validated['display_name'],
            'description' => $validated['description'],
            'icon' => $validated['icon'] ?? 'widgets_rounded',
            'icon_path' => $iconPath,
            'module_url' => $validated['module_url'],
            'test_mode_url' => $validated['test_mode_url'] ?? null,
            'version' => '1.0.0',
            'category' => $validated['category'] ?? null,
            'developer_name' => $developer->name,
            'developer_url' => $developer->website,
            'developer_id' => $developerId,
            'install_count' => 0,
            'is_active' => false,
            'is_featured' => false,
            'sort_order' => 100,
            'required_fields' => [],
            'screenshots' => $validated['screenshots'] ?? [],
            'required_bridge_apis' => $validated['required_bridge_apis'] ?? [],
            'review_status' => 'pending',
        ]);

        return redirect()->route('developer.dashboard')
            ->with('success', '"'.$module->display_name.'" submitted for review.');
    }

    public function showEdit(Module $module)
    {
        if ($module->developer_id !== session('developer_id')) {
            abort(403);
        }
        return view('developer.edit', ['app' => $module]);
    }

    public function update(Request $request, Module $module)
    {
        if ($module->developer_id !== session('developer_id')) {
            abort(403);
        }

        $validated = $request->validate([
            'display_name' => 'required|string|max:100',
            'description' => 'required|string|max:500',
            'module_url' => 'required|url|max:500',
            'test_mode_url' => 'nullable|url|max:500',
            'version' => 'required|string|max:20',
            'category' => 'nullable|string|max:100',
            'icon' => 'nullable|string|max:100',
            'icon_file' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'screenshots' => 'nullable|array|max:5',
            'screenshots.*' => 'url|max:500',
            'required_bridge_apis' => 'nullable|array',
            'required_bridge_apis.*' => 'string|max:100',
        ]);

        if ($request->hasFile('icon_file')) {
            // Delete old custom icon if it exists
            if ($module->icon_path && Storage::disk('public')->exists($module->icon_path)) {
                Storage::disk('public')->delete($module->icon_path);
            }
            $validated['icon_path'] = $request->file('icon_file')->store('store-icons', 'public');
        }

        // If developer clears the icon field and doesn't upload a new one, remove custom icon
        if (empty($validated['icon']) && !$request->hasFile('icon_file')) {
            if ($module->icon_path && Storage::disk('public')->exists($module->icon_path)) {
                Storage::disk('public')->delete($module->icon_path);
            }
            $validated['icon_path'] = null;
            $validated['icon'] = 'widgets_rounded';
        }

        $module->update($validated);

        return redirect()->route('developer.dashboard')
            ->with('success', '"'.$module->display_name.'" updated.');
    }
}
