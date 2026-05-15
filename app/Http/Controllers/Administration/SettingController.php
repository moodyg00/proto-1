<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\StoreSettingRequest;
use App\Models\Setting;
use App\Services\Administration\AdministrationService;
use App\Support\BrandSettings;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function __construct(private readonly AdministrationService $service) {}

    public function index(): Response|RedirectResponse
    {
        if (! auth()->check()) {
            return redirect('/admin/login');
        }

        $data = $this->service->listSettings(request()->only(['module']));

        return Inertia::render('Administration/Settings/Index', [
            ...$data,
            'branding' => BrandSettings::get(),
        ]);
    }

    public function store(StoreSettingRequest $request): RedirectResponse
    {
        if (! auth()->check()) {
            return redirect('/admin/login');
        }

        $this->service->createSetting($request->validated());

        return back()->with('success', 'Setting created.');
    }

    public function update(StoreSettingRequest $request, Setting $setting): RedirectResponse
    {
        if (! auth()->check()) {
            return redirect('/admin/login');
        }

        $this->service->updateSetting($setting, $request->validated());

        return back()->with('success', 'Setting updated.');
    }

    public function updateBranding(Request $request): RedirectResponse
    {
        if (! auth()->check()) {
            return redirect('/admin/login');
        }

        $validated = $request->validate([
            'brand_name' => ['required', 'string', 'max:120'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $setting = Setting::query()->firstOrNew([
            'module' => 'business',
            'key' => 'branding',
        ]);

        $current = is_array($setting->value) ? $setting->value : [];
        $logoPath = $current['logo_path'] ?? null;

        if ($request->hasFile('logo')) {
            if (filled($logoPath)) {
                Storage::disk('public')->delete($logoPath);
            }

            $logoPath = $request->file('logo')->store('branding', 'public');
        }

        $setting->fill([
            'module' => 'business',
            'key' => 'branding',
            'value' => [
                'brand_name' => $validated['brand_name'],
                'logo_path' => $logoPath,
            ],
            'description' => 'Business branding including the application name and header logo.',
        ]);
        $setting->save();

        BrandSettings::clearCache();

        return back()->with('success', 'Brand settings updated.');
    }
}
