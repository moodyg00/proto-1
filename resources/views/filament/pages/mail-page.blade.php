<x-filament-panels::page>
	@php($metrics = $this->getMailMetrics())
	@php($templates = $this->getMailTemplates())
	@php($campaigns = $this->getMailCampaigns())
	@php($segments = $this->getRecipientSegments())

	<div class="space-y-6">
		<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
			<x-filament::section>
				<x-slot name="heading">Reachable Contacts</x-slot>
				<p class="text-3xl font-semibold text-gray-900">{{ number_format($metrics['contacts_with_email']) }}</p>
				<p class="text-sm text-gray-600">Contacts with an email address ready for CRM outreach.</p>
			</x-filament::section>

			<x-filament::section>
				<x-slot name="heading">Open Follow-Ups</x-slot>
				<p class="text-3xl font-semibold text-gray-900">{{ number_format($metrics['lead_follow_ups']) }}</p>
				<p class="text-sm text-gray-600">Leads still in play with a scheduled follow-up touchpoint.</p>
			</x-filament::section>

			<x-filament::section>
				<x-slot name="heading">Mail Settings</x-slot>
				<p class="text-3xl font-semibold text-gray-900">{{ number_format($metrics['mail_settings']) }}</p>
				<p class="text-sm text-gray-600">CRM mail configuration entries stored in administration settings.</p>
			</x-filament::section>

			<x-filament::section>
				<x-slot name="heading">Templates</x-slot>
				<p class="text-3xl font-semibold text-gray-900">{{ number_format($metrics['template_count']) }}</p>
				<p class="text-sm text-gray-600">Reusable campaign and follow-up copy blocks stored in the CRM dashboard.</p>
			</x-filament::section>

			<x-filament::section>
				<x-slot name="heading">Scheduled Campaigns</x-slot>
				<p class="text-3xl font-semibold text-gray-900">{{ number_format($metrics['scheduled_campaigns']) }}</p>
				<p class="text-sm text-gray-600">Campaigns currently queued for a future send.</p>
			</x-filament::section>

			<x-filament::section>
				<x-slot name="heading">Recipient Segments</x-slot>
				<p class="text-3xl font-semibold text-gray-900">{{ number_format($metrics['segment_count']) }}</p>
				<p class="text-sm text-gray-600">Saved audiences for marketing, follow-up, and retention outreach.</p>
			</x-filament::section>
		</div>

		<div class="grid gap-6 xl:grid-cols-[1.2fr_1fr]">
			<x-filament::section>
				<x-slot name="heading">Campaign Queue</x-slot>
				<div class="space-y-3">
					@forelse ($campaigns as $campaign)
						<div class="rounded-xl border border-gray-200 px-4 py-3 dark:border-white/10">
							<div class="flex items-center justify-between gap-3">
								<div>
									<p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $campaign['name'] }}</p>
									<p class="text-sm text-gray-600 dark:text-gray-300">{{ $campaign['template'] }} · {{ $campaign['audience'] }}</p>
								</div>
								<div class="text-right">
									<p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ str($campaign['status'])->headline() }}</p>
									<p class="text-sm text-gray-600 dark:text-gray-300">{{ filled($campaign['send_at'] ?? null) ? \Illuminate\Support\Carbon::parse($campaign['send_at'])->format('M j, Y g:i A') : 'Not scheduled' }}</p>
								</div>
							</div>
						</div>
					@empty
						<p class="text-sm text-gray-600 dark:text-gray-300">No campaigns configured yet.</p>
					@endforelse
				</div>
			</x-filament::section>

			<div class="space-y-6">
				<x-filament::section>
					<x-slot name="heading">Templates</x-slot>
					<div class="space-y-3">
						@forelse ($templates as $template)
							<div class="rounded-xl border border-gray-200 px-4 py-3 dark:border-white/10">
								<p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $template['name'] }}</p>
								<p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ str($template['channel'])->headline() }}</p>
								<p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $template['subject'] }}</p>
							</div>
						@empty
							<p class="text-sm text-gray-600 dark:text-gray-300">No templates configured yet.</p>
						@endforelse
					</div>
				</x-filament::section>

				<x-filament::section>
					<x-slot name="heading">Smart Recipient Filters</x-slot>
					<div class="space-y-3">
						@foreach ($segments as $segment)
							<div class="rounded-xl border border-gray-200 px-4 py-3 dark:border-white/10">
								<div class="flex items-center justify-between gap-3">
									<p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $segment['label'] }}</p>
									<p class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($segment['count']) }}</p>
								</div>
								<p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $segment['description'] }}</p>
							</div>
						@endforeach
					</div>
				</x-filament::section>
			</div>
		</div>
	</div>
</x-filament-panels::page>