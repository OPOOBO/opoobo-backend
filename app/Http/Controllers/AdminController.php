<?php

namespace App\Http\Controllers;

use App\Models\Developer;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function showLogin()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $adminEmails = config('miniapp.admin_emails', []);

        if (!in_array($request->email, $adminEmails)) {
            return back()->withErrors(['email' => 'This email is not an admin account.']);
        }

        $developer = Developer::where('email', $request->email)->first();

        if (!$developer || !Hash::check($request->password, $developer->password)) {
            return back()->withErrors(['email' => 'Invalid credentials.']);
        }

        session([
            'developer_id' => $developer->id,
            'developer_email' => $developer->email,
            'developer_name' => $developer->name,
            'login_via' => 'admin',
        ]);

        return redirect()->route('admin.dashboard');
    }

    public function logout()
    {
        session()->forget(['developer_id', 'developer_email', 'developer_name', 'login_via']);
        return redirect()->route('admin.login');
    }

    public function dashboard()
    {
        $pending = Module::where('is_active', false)
            ->whereNotNull('module_url')
            ->orderByDesc('created_at')
            ->get();

        $active = Module::where('is_active', true)
            ->whereNotNull('module_url')
            ->orderByDesc('install_count')
            ->get();

        $totalApps = Module::count();
        $totalDevelopers = \App\Models\Developer::count();

        return view('admin.dashboard', compact('pending', 'active', 'totalApps', 'totalDevelopers'));
    }

    public function profile()
    {
        $developer = Developer::find(session('developer_id'));
        $appCount = Module::count();
        return view('admin.profile', compact('developer', 'appCount'));
    }

    public function approve(Module $module)
    {
        $module->update([
            'is_active' => true,
            'review_status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => session('developer_name') ?? 'admin',
        ]);
        return back()->with('success', '"'.$module->display_name.'" approved and live.');
    }

    public function reject(Module $module)
    {
        $module->update([
            'is_active' => false,
            'review_status' => 'rejected',
            'reviewed_at' => now(),
            'reviewed_by' => session('developer_name') ?? 'admin',
        ]);
        return back()->with('success', '"'.$module->display_name.'" rejected.');
    }

    public function feature(Module $module)
    {
        $module->update(['is_featured' => !$module->is_featured]);
        $status = $module->is_featured ? 'featured' : 'unfeatured';
        return back()->with('success', '"'.$module->display_name.'" '.$status.'.');
    }

    public function destroy(Module $module)
    {
        $name = $module->display_name;
        $module->delete();
        return back()->with('success', '"'.$name.'" deleted.');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'required|integer|exists:modules,id',
        ]);

        foreach ($request->order as $index => $moduleId) {
            Module::where('id', $moduleId)->update(['sort_order' => $index]);
        }

        return back()->with('success', 'Module order updated.');
    }

    public function updateModule(Request $request, Module $module)
    {
        $validated = $request->validate([
            'sort_order' => 'nullable|integer|min:0',
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'review_notes' => 'nullable|string|max:1000',
        ]);

        $module->update($validated);
        return back()->with('success', '"'.$module->display_name.'" updated.');
    }

    /**
     * Run pre-flight check on a module's URL.
     */
    public function preflight(Module $module)
    {
        $url = $module->module_url;
        if (!$url) {
            return back()->with('error', 'No URL to check.');
        }

        $result = [
            'ssl_valid' => false,
            'url_loads' => false,
            'load_time_ms' => 0,
            'status_code' => 0,
        ];

        $parsedUrl = parse_url($url);
        $result['ssl_valid'] = ($parsedUrl['scheme'] ?? '') === 'https';

        if ($result['ssl_valid']) {
            try {
                $start = microtime(true);
                $response = Http::timeout(10)
                    ->withHeaders(['User-Agent' => 'OPOOBO-MiniApp-Checker/1.0'])
                    ->get($url);
                $result['load_time_ms'] = (int) ((microtime(true) - $start) * 1000);
                $result['status_code'] = $response->status();
                $result['url_loads'] = $response->successful();
            } catch (\Exception $e) {
                Log::warning('Preflight check failed', ['module' => $module->id, 'error' => $e->getMessage()]);
            }
        }

        $module->update([
            'ssl_valid' => $result['ssl_valid'],
            'url_loads' => $result['url_loads'],
            'last_preflight_status' => ($result['ssl_valid'] && $result['url_loads']) ? 'passed' : 'failed',
            'last_preflight_at' => now(),
        ]);

        $status = ($result['ssl_valid'] && $result['url_loads']) ? 'passed' : 'failed';
        return back()->with('success', "Preflight {$status}: SSL " . ($result['ssl_valid'] ? '✓' : '✗') . ', Loads ' . ($result['url_loads'] ? '✓' : '✗') . " ({$result['load_time_ms']}ms)");
    }

    /**
     * Mark that admin has tested this app.
     */
    public function markTested(Module $module)
    {
        $module->update([
            'admin_tested' => true,
            'admin_tested_at' => now(),
        ]);
        return back()->with('success', '"'.$module->display_name.'" marked as tested.');
    }

    /**
     * Update review notes for a module.
     */
    public function updateReviewNotes(Request $request, Module $module)
    {
        $validated = $request->validate([
            'review_notes' => 'nullable|string|max:1000',
        ]);

        $module->update(['review_notes' => $validated['review_notes'] ?? null]);
        return back()->with('success', 'Review notes updated.');
    }
}
