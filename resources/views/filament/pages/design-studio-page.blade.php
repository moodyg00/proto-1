<x-filament-panels::page>
    @php($designs = $this->getDesigns())
    @php($selectedDesign = $this->getSelectedDesign())
    @php($artboard = $this->getArtboardData())
    @php($assetLibrary = $this->getAssetLibrary())
    @php($selectedAssets = $this->getSelectedAssetLibrary())
    @php($stats = $this->getDesignStats())

    <style>
        .design-studio-shell {
            display: grid;
            gap: 1.5rem;
        }

        .design-studio-grid {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: 22rem minmax(0, 1fr) 22rem;
        }

        .design-card {
            border-radius: 1.25rem;
            border: 1px solid rgba(148, 163, 184, .22);
            background: linear-gradient(180deg, rgba(255,255,255,.95), rgba(248,250,252,.92));
            padding: 1rem;
            box-shadow: 0 18px 40px rgba(15, 23, 42, .05);
        }

        .artboard-wrap {
            display: flex;
            min-height: 34rem;
            align-items: center;
            justify-content: center;
            border-radius: 1.5rem;
            background:
                radial-gradient(circle at top, rgba(253, 224, 71, .24), transparent 28%),
                linear-gradient(135deg, rgba(15,23,42,.03), rgba(8,145,178,.10));
        }

        .artboard {
            position: relative;
            border-radius: 1.25rem;
            border: 2px solid rgba(15, 118, 110, .28);
            background:
                linear-gradient(90deg, rgba(15, 118, 110, .08) 1px, transparent 1px),
                linear-gradient(rgba(15, 118, 110, .08) 1px, transparent 1px),
                #fffef8;
            background-size: 24px 24px, 24px 24px, auto;
            overflow: hidden;
        }

        .artboard-overlay {
            position: absolute;
            inset: 1.25rem;
            border: 1px dashed rgba(244, 114, 182, .55);
            border-radius: 1rem;
            display: grid;
            place-items: center;
            color: #475569;
            font-size: .9rem;
            text-align: center;
            background: rgba(255,255,255,.72);
        }

        .design-link {
            display: block;
            border-radius: 1rem;
            border: 1px solid rgba(148, 163, 184, .22);
            padding: .85rem .95rem;
            text-decoration: none;
        }

        .design-link.is-active {
            border-color: rgba(13, 148, 136, .55);
            background: rgba(204, 251, 241, .42);
        }

        .asset-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: .25rem .65rem;
            background: rgba(15, 118, 110, .12);
            color: #0f766e;
            font-size: .75rem;
            font-weight: 600;
        }

        @media (max-width: 1280px) {
            .design-studio-grid { grid-template-columns: 1fr; }
        }
    </style>

    <div class="design-studio-shell">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-filament::section>
                <x-slot name="heading">Designs</x-slot>
                <p class="text-3xl font-semibold text-gray-900 dark:text-white">{{ number_format($stats['total']) }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-300">Active design briefs inside the studio.</p>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Approved</x-slot>
                <p class="text-3xl font-semibold text-gray-900 dark:text-white">{{ number_format($stats['approved']) }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-300">Ready for production or handoff.</p>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Drafts</x-slot>
                <p class="text-3xl font-semibold text-gray-900 dark:text-white">{{ number_format($stats['draft']) }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-300">Layouts still iterating inside the artboard.</p>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Versions</x-slot>
                <p class="text-3xl font-semibold text-gray-900 dark:text-white">{{ number_format($stats['versions']) }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-300">Historical versions recorded for production review.</p>
            </x-filament::section>
        </div>

        <div class="design-studio-grid">
            <x-filament::section>
                <x-slot name="heading">Design Queue</x-slot>
                <div class="space-y-3">
                    @forelse ($designs as $design)
                        <a href="{{ \App\Filament\Pages\DesignStudioPage::getUrl(['design' => $design['id']]) }}" class="design-link {{ optional($selectedDesign)?->getKey() === $design['id'] ? 'is-active' : '' }}">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $design['name'] }}</p>
                                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ \Illuminate\Support\Str::headline($design['design_type']) }}</p>
                                </div>
                                <span class="asset-pill">{{ \Illuminate\Support\Str::headline($design['status']) }}</span>
                            </div>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $design['dimensions'] }} artboard</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Updated {{ $design['updated_at'] }}</p>
                        </a>
                    @empty
                        <p class="text-sm text-gray-600 dark:text-gray-300">No physical designs yet. Use <strong>New Design</strong> to start the studio.</p>
                    @endforelse
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Artboard</x-slot>

                @if ($selectedDesign)
                    <div class="mb-4 flex flex-wrap items-center gap-3">
                        <span class="asset-pill">{{ \Illuminate\Support\Str::headline($selectedDesign->design_type) }}</span>
                        <span class="asset-pill">{{ $artboard['label'] }}</span>
                        <span class="asset-pill">{{ \Illuminate\Support\Str::headline($selectedDesign->status ?: 'draft') }}</span>
                    </div>

                    <div class="artboard-wrap">
                        <div class="artboard" style="width: {{ $artboard['width'] }}px; height: {{ $artboard['height'] }}px;">
                            <div class="artboard-overlay">
                                <div>
                                    <strong>{{ $selectedDesign->name }}</strong>
                                    <div class="mt-2">{{ $selectedDesign->description ?: 'Use the edit action to assign artboard assets, preview URLs, and production PDFs.' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        <div class="design-card">
                            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Linked Assets</p>
                            <div class="mt-3 space-y-2">
                                @forelse ($selectedAssets as $asset)
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $asset['name'] ?: $asset['file_name'] }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $asset['file_name'] }} · {{ $asset['mime_type'] ?: 'Unknown type' }}</p>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-600 dark:text-gray-300">No asset-library items are attached to this design yet.</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="design-card">
                            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Production Files</p>
                            <div class="mt-3 space-y-2 text-sm text-gray-700 dark:text-gray-200">
                                <p><strong>Preview:</strong> {{ data_get($selectedDesign->files, 'preview_url') ?: 'Not set' }}</p>
                                <p><strong>Production PDF:</strong> {{ data_get($selectedDesign->files, 'production_pdf') ?: data_get($selectedDesign->files, 'pdf') ?: 'Not set' }}</p>
                                <p><strong>Latest Version:</strong> {{ $selectedDesign->latest_version_id ?: 'None yet' }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-600 dark:text-gray-300">Select a design from the queue to open the studio artboard.</p>
                @endif
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Asset Library</x-slot>
                <div class="space-y-3">
                    @forelse ($assetLibrary as $asset)
                        <div class="design-card">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $asset['name'] ?: $asset['file_name'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $asset['file_name'] }}</p>
                            <p class="mt-2 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $asset['mime_type'] ?: 'Unknown type' }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-600 dark:text-gray-300">No media-library assets exist yet. Once files are uploaded into the asset library, they will appear here for design selection.</p>
                    @endforelse
                </div>
            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>