<x-filament-panels::page>
	<div class="space-y-6">
		<x-filament::section>
			<x-slot name="heading">Operations Dashboard</x-slot>
			<p class="text-sm text-gray-600">
				Track field operations workflows and keep work execution organized.
			</p>
		</x-filament::section>

		<div class="grid gap-4 md:grid-cols-3">
			<x-filament::section>
				<x-slot name="heading">Jobs</x-slot>
			</x-filament::section>
			<x-filament::section>
				<x-slot name="heading">Schedule</x-slot>
			</x-filament::section>
			<x-filament::section>
				<x-slot name="heading">Tasks</x-slot>
			</x-filament::section>
		</div>
	</div>
</x-filament-panels::page>
