<x-filament-panels::page>
	@php($kpis = $this->getKpis())
	@php($recentInvoices = $this->getRecentInvoices())
	@php($recentBills = $this->getRecentBills())

	<div class="space-y-6">
		<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
			<x-filament::section>
				<x-slot name="heading">Open Invoice Balance</x-slot>
				<p class="text-3xl font-semibold text-gray-900">${{ number_format($kpis['open_invoice_balance'], 2) }}</p>
				<p class="text-sm text-gray-600">Outstanding customer receivables waiting to be collected.</p>
			</x-filament::section>

			<x-filament::section>
				<x-slot name="heading">Unpaid Bills</x-slot>
				<p class="text-3xl font-semibold text-gray-900">${{ number_format($kpis['unpaid_bills'], 2) }}</p>
				<p class="text-sm text-gray-600">Vendor obligations still open in the current AP queue.</p>
			</x-filament::section>

			<x-filament::section>
				<x-slot name="heading">Journal Entries This Month</x-slot>
				<p class="text-3xl font-semibold text-gray-900">{{ number_format($kpis['journal_entries_this_month']) }}</p>
				<p class="text-sm text-gray-600">Posted entries across accounting, invoicing, banking, and operations.</p>
			</x-filament::section>

			<x-filament::section>
				<x-slot name="heading">Pending Bank Transactions</x-slot>
				<p class="text-3xl font-semibold text-gray-900">{{ number_format($kpis['uncategorized_transactions']) }}</p>
				<p class="text-sm text-gray-600">Bank items that still need categorization or journal linkage.</p>
			</x-filament::section>
		</div>

		<div class="grid gap-6 xl:grid-cols-2">
			<x-filament::section>
				<x-slot name="heading">Recent Invoices</x-slot>
				<div class="space-y-3">
					@forelse ($recentInvoices as $invoice)
						<div class="flex items-center justify-between gap-4 rounded-xl border border-gray-200 px-4 py-3 dark:border-white/10">
							<div>
								<p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $invoice['number'] ?: 'Draft invoice' }}</p>
								<p class="text-sm text-gray-600 dark:text-gray-300">{{ $invoice['contact'] ?: 'No contact' }} · {{ $invoice['issue_date'] ?: 'No issue date' }}</p>
							</div>
							<div class="text-right">
								<p class="text-sm font-semibold text-gray-900 dark:text-white">${{ number_format($invoice['amount_due'], 2) }}</p>
								<p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ str($invoice['status'])->headline() }}</p>
							</div>
						</div>
					@empty
						<p class="text-sm text-gray-600 dark:text-gray-300">No invoice activity yet.</p>
					@endforelse
				</div>
			</x-filament::section>

			<x-filament::section>
				<x-slot name="heading">Recent Bills</x-slot>
				<div class="space-y-3">
					@forelse ($recentBills as $bill)
						<div class="flex items-center justify-between gap-4 rounded-xl border border-gray-200 px-4 py-3 dark:border-white/10">
							<div>
								<p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $bill['number'] ?: 'Draft bill' }}</p>
								<p class="text-sm text-gray-600 dark:text-gray-300">{{ $bill['vendor'] ?: 'No vendor' }} · {{ $bill['issue_date'] ?: 'No issue date' }}</p>
							</div>
							<div class="text-right">
								<p class="text-sm font-semibold text-gray-900 dark:text-white">${{ number_format($bill['total_amount'], 2) }}</p>
								<p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ str($bill['status'])->headline() }}</p>
							</div>
						</div>
					@empty
						<p class="text-sm text-gray-600 dark:text-gray-300">No bill activity yet.</p>
					@endforelse
				</div>
			</x-filament::section>
		</div>
	</div>
</x-filament-panels::page>
