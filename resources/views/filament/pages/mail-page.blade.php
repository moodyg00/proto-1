<x-filament-panels::page>
	@php($metrics = $this->getMailMetrics())

	<div class="space-y-6">
		<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
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
				<x-slot name="heading">Email Activities</x-slot>
				<p class="text-3xl font-semibold text-gray-900">{{ number_format($metrics['email_activities']) }}</p>
				<p class="text-sm text-gray-600">Activity records already tagged as email work.</p>
			</x-filament::section>

			<x-filament::section>
				<x-slot name="heading">Mail Settings</x-slot>
				<p class="text-3xl font-semibold text-gray-900">{{ number_format($metrics['mail_settings']) }}</p>
				<p class="text-sm text-gray-600">CRM mail configuration entries stored in administration settings.</p>
			</x-filament::section>
		</div>

		<x-filament::section>
			<x-slot name="heading">Mail Workspace</x-slot>
			<p class="text-sm text-gray-600">
				Proto-1 does not have a dedicated inbound or outbound mail table yet, so this page currently acts as the CRM mail control point.
				Use it to monitor reachable contacts, follow-up load, and settings coverage while the deeper inbox/outreach module is wired onto the canonical APP-LAB schema.
			</p>
		</x-filament::section>
	</div>
</x-filament-panels::page>