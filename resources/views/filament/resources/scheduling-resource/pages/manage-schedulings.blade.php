<x-filament-panels::page>
	<x-filament::section>
		<x-slot name="heading">Calendar Filters</x-slot>

		<form method="GET" class="grid gap-4 md:grid-cols-[minmax(0,18rem)_auto] md:items-end">
			<div class="grid gap-4 md:grid-cols-2">
				<div>
					<label for="booking_type" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Booking type</label>
					<select id="booking_type" name="booking_type" class="fi-input block w-full rounded-lg border-gray-300 bg-white px-3 py-2 text-sm shadow-sm dark:border-white/10 dark:bg-gray-900 dark:text-white">
						<option value="">All booking types</option>
						@foreach ($this->getBookingTypeOptions() as $value => $label)
							<option value="{{ $value }}" @selected(request()->query('booking_type') === (string) $value)>{{ $label }}</option>
						@endforeach
					</select>
				</div>

				<div>
					<label for="availability_scope" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Availability overlay</label>
					<select id="availability_scope" name="availability_scope" class="fi-input block w-full rounded-lg border-gray-300 bg-white px-3 py-2 text-sm shadow-sm dark:border-white/10 dark:bg-gray-900 dark:text-white">
						@foreach ($this->getAvailabilityScopeOptions() as $value => $label)
							<option value="{{ $value }}" @selected(request()->query('availability_scope', 'all') === (string) $value)>{{ $label }}</option>
						@endforeach
					</select>
				</div>
			</div>

			<div class="flex gap-3">
				<button type="submit" class="fi-btn fi-btn-color-primary inline-flex items-center justify-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500">
					Apply filter
				</button>

				@if (filled(request()->query('booking_type')) || request()->query('availability_scope', 'all') !== 'all')
					<a href="{{ \App\Filament\Resources\SchedulingResource::getUrl('index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-white/10 dark:text-gray-200 dark:hover:bg-white/5">
						Clear
					</a>
				@endif
			</div>
		</form>
	</x-filament::section>
</x-filament-panels::page>