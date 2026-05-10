<x-filament-panels::page>
	@php
		$maxRevenue = max((float) collect($this->heroStats)->max('value'), 1);
	@endphp
	<style>
		.fi-layout,
		.fi-main-ctn,
		.fi-main,
		.fi-page,
		.crm-dashboard {
			overflow-y: visible;
			overscroll-behavior-y: auto;
		}

		.fi-main-ctn,
		.fi-main,
		.fi-page,
		.crm-dashboard {
			touch-action: pan-y;
			-webkit-overflow-scrolling: touch;
		}

		.crm-dashboard {
			--crm-surface-base: var(--app-surface-panel);
			--crm-surface-raised: var(--app-surface-raised);
			--crm-surface-elevated: var(--app-surface-elevated);
			--crm-surface-inset: var(--app-surface-inset);
			--crm-border-soft: var(--app-border-soft);
			--crm-border-strong: var(--app-border-strong);
			--crm-text: var(--app-text);
			--crm-text-muted: var(--app-text-muted);
			--crm-text-subtle: var(--app-text-subtle);
			--crm-shadow-panel: var(--app-shadow-panel);
			--crm-shadow-raised: var(--app-shadow-raised);
		}

		.crm-dashboard {
			display: grid;
			gap: 24px;
			color: var(--crm-text);
		}

		.crm-dashboard__header {
			display: flex;
			align-items: flex-end;
			justify-content: space-between;
			gap: 16px;
			flex-wrap: wrap;
		}

		.crm-dashboard__eyebrow {
			font-size: 0.75rem;
			font-weight: 700;
			letter-spacing: 0.24em;
			text-transform: uppercase;
			color: var(--crm-text-subtle);
		}

		.crm-dashboard__subcopy {
			margin-top: 8px;
			font-size: 0.95rem;
			color: var(--crm-text-muted);
		}

		.crm-dashboard__filters {
			display: flex;
			align-items: center;
			gap: 12px;
			flex-wrap: wrap;
		}

		.crm-dashboard__input {
			min-width: 15rem;
			padding: 14px 16px;
			border-radius: 18px;
			border: 1px solid var(--crm-border-strong);
			background: var(--crm-surface-base);
			color: var(--crm-text);
			box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.03);
			color-scheme: dark;
		}

		.crm-dashboard__button,
		.crm-dashboard__button:link,
		.crm-dashboard__button:visited {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			padding: 14px 20px;
			border-radius: 18px;
			font-size: 0.95rem;
			font-weight: 600;
			text-decoration: none;
			transition: background-color 120ms ease, border-color 120ms ease, color 120ms ease;
		}

		.crm-dashboard__button--ghost {
			border: 1px solid var(--crm-border-strong);
			background: transparent;
			color: var(--crm-text);
		}

		.crm-dashboard__button--primary {
			border: 1px solid #f7b84b;
			background: #f7b84b;
			color: #111827;
		}

		.crm-dashboard__panel {
			border: 1px solid var(--crm-border-soft);
			border-radius: 28px;
			background: var(--crm-surface-base);
			box-shadow: var(--crm-shadow-panel), inset 0 1px 0 rgba(255, 255, 255, 0.02);
		}

		.crm-dashboard__hero {
			padding: 24px;
		}

		.crm-dashboard__hero-grid {
			display: grid;
			gap: 24px;
			grid-template-columns: 20rem minmax(0, 1fr);
		}

		.crm-dashboard__hero-stack {
			display: grid;
			gap: 16px;
		}

		.crm-dashboard__stat-card {
			border: 1px solid var(--crm-border-soft);
			border-radius: 24px;
			padding: 24px;
			background: var(--crm-surface-raised);
			box-shadow: var(--crm-shadow-raised);
		}

		.crm-dashboard__stat-label {
			font-size: 0.95rem;
			color: var(--crm-text-muted);
		}

		.crm-dashboard__stat-row {
			display: flex;
			align-items: baseline;
			gap: 14px;
			margin-top: 16px;
		}

		.crm-dashboard__stat-value {
			font-size: 2.3rem;
			line-height: 1;
			font-weight: 700;
		}

		.crm-dashboard__stat-value--won {
			color: #22c55e;
		}

		.crm-dashboard__stat-value--lost {
			color: #fb7185;
		}

		.crm-dashboard__stat-share {
			font-size: 1rem;
			font-weight: 700;
			color: #4ade80;
		}

		.crm-dashboard__chart {
			border: 1px solid var(--crm-border-soft);
			border-radius: 24px;
			padding: 24px;
			background: var(--crm-surface-raised);
			box-shadow: var(--crm-shadow-raised);
		}

		.crm-dashboard__bar-group {
			display: grid;
			gap: 22px;
		}

		.crm-dashboard__bar-meta {
			display: flex;
			justify-content: space-between;
			gap: 12px;
			margin-bottom: 10px;
			font-size: 0.95rem;
			color: var(--crm-text-muted);
		}

		.crm-dashboard__bar-track {
			height: 58px;
			padding: 10px;
			border: 1px solid rgba(255, 255, 255, 0.06);
			border-radius: 22px;
			background: var(--crm-surface-inset);
			box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.02);
		}

		.crm-dashboard__bar-fill {
			height: 100%;
			border-radius: 14px;
		}

		.crm-dashboard__bar-fill--won {
			background: linear-gradient(90deg, #22c55e, #16a34a);
		}

		.crm-dashboard__bar-fill--lost {
			background: linear-gradient(90deg, #ef4444, #fb7185);
		}

		.crm-dashboard__legend {
			display: flex;
			gap: 24px;
			flex-wrap: wrap;
			margin-top: 24px;
			padding-top: 20px;
			border-top: 1px solid var(--crm-border-soft);
			color: var(--crm-text-muted);
		}

		.crm-dashboard__legend-item {
			display: inline-flex;
			align-items: center;
			gap: 10px;
		}

		.crm-dashboard__legend-dot {
			width: 16px;
			height: 16px;
			border-radius: 4px;
		}

		.crm-dashboard__legend-dot--won {
			background: #22c55e;
		}

		.crm-dashboard__legend-dot--lost {
			background: #ef4444;
		}

		.crm-dashboard__metrics {
			display: grid;
			gap: 16px;
			grid-template-columns: repeat(3, minmax(0, 1fr));
		}

		.crm-dashboard__metric,
		.crm-dashboard__card {
			padding: 24px;
		}

		.crm-dashboard__metric-title,
		.crm-dashboard__section-kicker {
			font-size: 0.95rem;
			color: var(--crm-text-muted);
		}

		.crm-dashboard__metric-value {
			margin-top: 16px;
			font-size: 2.3rem;
			font-weight: 700;
			line-height: 1;
			color: var(--crm-text);
		}

		.crm-dashboard__metric-meta {
			margin-top: 12px;
			font-size: 0.95rem;
			color: var(--crm-text-subtle);
		}

		.crm-dashboard__columns {
			display: grid;
			gap: 24px;
			grid-template-columns: minmax(0, 1.7fr) minmax(19rem, 1fr);
		}

		.crm-dashboard__stack,
		.crm-dashboard__column-stack,
		.crm-dashboard__card-list {
			display: grid;
			gap: 24px;
		}

		.crm-dashboard__split {
			display: grid;
			gap: 24px;
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}

		.crm-dashboard__card-header {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 12px;
			margin-bottom: 20px;
		}

		.crm-dashboard__card-title {
			font-size: 1.15rem;
			font-weight: 700;
			color: var(--crm-text);
		}

		.crm-dashboard__card-note {
			font-size: 0.75rem;
			font-weight: 700;
			letter-spacing: 0.24em;
			text-transform: uppercase;
			color: var(--crm-text-subtle);
		}

		.crm-dashboard__list-item {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 16px;
			padding: 16px 18px;
			border: 1px solid var(--crm-border-soft);
			border-radius: 20px;
			background: var(--crm-surface-elevated);
			box-shadow: var(--crm-shadow-raised);
		}

		.crm-dashboard__list-item--person {
			justify-content: flex-start;
		}

		.crm-dashboard__avatar {
			width: 44px;
			height: 44px;
			border-radius: 999px;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			background: rgba(247, 184, 75, 0.15);
			color: #f7b84b;
			font-weight: 700;
			box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
		}

		.crm-dashboard__item-main {
			font-weight: 600;
			color: var(--crm-text);
		}

		.crm-dashboard__item-sub {
			margin-top: 4px;
			font-size: 0.85rem;
			color: var(--crm-text-subtle);
		}

		.crm-dashboard__item-value {
			font-weight: 700;
			color: var(--crm-text);
			margin-left: auto;
		}

		.crm-dashboard__meter {
			display: grid;
			gap: 12px;
		}

		.crm-dashboard__meter-row {
			display: grid;
			gap: 8px;
		}

		.crm-dashboard__meter-meta {
			display: flex;
			justify-content: space-between;
			gap: 12px;
			font-size: 0.95rem;
			color: var(--crm-text-muted);
		}

		.crm-dashboard__meter-track {
			height: 8px;
			border-radius: 999px;
			background: rgba(255, 255, 255, 0.08);
			overflow: hidden;
		}

		.crm-dashboard__meter-fill {
			height: 100%;
			border-radius: 999px;
		}

		.crm-dashboard__meter-fill--amber {
			background: #f59e0b;
		}

		.crm-dashboard__meter-fill--sky {
			background: #38bdf8;
		}

		.crm-dashboard__meter-fill--slate {
			background: #94a3b8;
		}

		.crm-dashboard__meter-fill--emerald {
			background: #22c55e;
		}

		.crm-dashboard__meter-fill--rose {
			background: #fb7185;
		}

		.crm-dashboard__empty {
			padding: 32px 20px;
			border: 1px dashed var(--crm-border-strong);
			border-radius: 20px;
			color: var(--crm-text-subtle);
			text-align: center;
		}

		@media (max-width: 1200px) {
			.crm-dashboard__hero-grid,
			.crm-dashboard__columns,
			.crm-dashboard__split,
			.crm-dashboard__metrics {
				grid-template-columns: 1fr;
			}
		}

		@media (max-width: 680px) {
			.crm-dashboard__input {
				min-width: 100%;
			}

			.crm-dashboard__filters {
				width: 100%;
			}

			.crm-dashboard__button,
			.crm-dashboard__button:link,
			.crm-dashboard__button:visited {
				flex: 1 1 auto;
			}
		}
	</style>
	<div class="crm-dashboard">
		<section class="crm-dashboard__header">
			<div>
				<p class="crm-dashboard__eyebrow">Customer Relations</p>
				<p class="crm-dashboard__subcopy">Revenue, lead velocity, and channel performance modeled after the Krayin dashboard.</p>
			</div>

			<form method="GET" action="{{ \App\Filament\Pages\CrmDashboard::getUrl() }}" class="crm-dashboard__filters">
				<input type="date" name="start_date" value="{{ $this->startDate }}" class="crm-dashboard__input">
				<input type="date" name="end_date" value="{{ $this->endDate }}" class="crm-dashboard__input">
				<a href="{{ \App\Filament\Pages\CrmDashboard::getUrl() }}" class="crm-dashboard__button crm-dashboard__button--ghost">Reset</a>
				<button type="submit" class="crm-dashboard__button crm-dashboard__button--primary">Apply</button>
			</form>
		</section>

		<section class="crm-dashboard__panel crm-dashboard__hero">
			<div class="crm-dashboard__hero-grid">
				<div class="crm-dashboard__hero-stack">
					@foreach ($this->heroStats as $stat)
						<article class="crm-dashboard__stat-card">
							<p class="crm-dashboard__stat-label">{{ $stat['label'] }}</p>
							<div class="crm-dashboard__stat-row">
								<p class="crm-dashboard__stat-value {{ $stat['tone'] === 'emerald' ? 'crm-dashboard__stat-value--won' : 'crm-dashboard__stat-value--lost' }}">{{ $this->formatMoney($stat['value']) }}</p>
								<span class="crm-dashboard__stat-share">{{ $stat['share'] }}%</span>
							</div>
						</article>
					@endforeach
				</div>

				<div class="crm-dashboard__chart">
					<div class="crm-dashboard__bar-group">
						@foreach ($this->heroStats as $stat)
							<div>
								<div class="crm-dashboard__bar-meta">
									<span>{{ $stat['label'] }}</span>
									<span>{{ $this->formatMoney($stat['value']) }}</span>
								</div>
								<div class="crm-dashboard__bar-track">
									<div class="crm-dashboard__bar-fill {{ $stat['tone'] === 'emerald' ? 'crm-dashboard__bar-fill--won' : 'crm-dashboard__bar-fill--lost' }}" style="width: {{ max(12, round(($stat['value'] / $maxRevenue) * 100)) }}%"></div>
								</div>
							</div>
						@endforeach
					</div>

					<div class="crm-dashboard__legend">
						<span class="crm-dashboard__legend-item"><span class="crm-dashboard__legend-dot crm-dashboard__legend-dot--won"></span>Won Revenue</span>
						<span class="crm-dashboard__legend-item"><span class="crm-dashboard__legend-dot crm-dashboard__legend-dot--lost"></span>Lost Revenue</span>
					</div>
				</div>
			</div>
		</section>

		<section class="crm-dashboard__metrics">
			@foreach ($this->metricCards as $card)
				<article class="crm-dashboard__panel crm-dashboard__metric">
					<p class="crm-dashboard__metric-title">{{ $card['label'] }}</p>
					<p class="crm-dashboard__metric-value">{{ $card['value'] }}</p>
					<p class="crm-dashboard__metric-meta">{{ $card['meta'] }}</p>
				</article>
			@endforeach
		</section>

		<section class="crm-dashboard__columns">
			<div class="crm-dashboard__stack">
				<div class="crm-dashboard__split">
					<article class="crm-dashboard__panel crm-dashboard__card">
						<div class="crm-dashboard__card-header">
							<h3 class="crm-dashboard__card-title">Top Organizations</h3>
							<span class="crm-dashboard__card-note">Value ranked</span>
						</div>
						<div class="crm-dashboard__card-list">
							@forelse ($this->topOrganizations as $organization)
								<div class="crm-dashboard__list-item">
									<div>
										<div class="crm-dashboard__item-main">{{ $organization['name'] }}</div>
										<div class="crm-dashboard__item-sub">{{ $organization['lead_count'] }} leads in range</div>
									</div>
									<div class="crm-dashboard__item-value">{{ $this->formatMoney($organization['value']) }}</div>
								</div>
							@empty
								<div class="crm-dashboard__empty">No organizations were active in this reporting window.</div>
							@endforelse
						</div>
					</article>

					<article class="crm-dashboard__panel crm-dashboard__card">
						<div class="crm-dashboard__card-header">
							<h3 class="crm-dashboard__card-title">Top People</h3>
							<h3 class="crm-dashboard__card-note">Lead value ranked</h3>
						</div>
						<div class="crm-dashboard__card-list">
							@forelse ($this->topPeople as $person)
								<div class="crm-dashboard__list-item crm-dashboard__list-item--person">
									<div class="crm-dashboard__avatar">{{ $person['initials'] ?: 'NA' }}</div>
									<div>
										<div class="crm-dashboard__item-main">{{ $person['name'] }}</div>
										<div class="crm-dashboard__item-sub">{{ $person['organization'] ?: 'No organization linked' }}</div>
									</div>
									<div class="crm-dashboard__item-value">{{ $this->formatMoney($person['value']) }}</div>
								</div>
							@empty
								<div class="crm-dashboard__empty">No leads were created in this reporting window.</div>
							@endforelse
						</div>
					</article>
				</div>

				<article class="crm-dashboard__panel crm-dashboard__card">
					<div class="crm-dashboard__card-header">
						<h3 class="crm-dashboard__card-title">Recent Leads</h3>
						<span class="crm-dashboard__card-note">Newest first</span>
					</div>
					<div class="crm-dashboard__card-list">
						@forelse ($this->recentLeads as $lead)
							<div class="crm-dashboard__list-item">
								<div>
									<div class="crm-dashboard__item-main">{{ $lead['name'] }}</div>
									<div class="crm-dashboard__item-sub">{{ $lead['organization'] ?: 'No organization' }} · {{ $lead['source'] }}</div>
								</div>
								<div style="text-align: right;">
									<div class="crm-dashboard__item-value">{{ $this->formatMoney($lead['value']) }}</div>
									<div class="crm-dashboard__item-sub">{{ $lead['status'] }}</div>
								</div>
							</div>
						@empty
							<div class="crm-dashboard__empty">No recent leads found for the selected interval.</div>
						@endforelse
					</div>
				</article>
			</div>

			<div class="crm-dashboard__column-stack">
				<article class="crm-dashboard__panel crm-dashboard__card">
					<div class="crm-dashboard__card-header">
						<h3 class="crm-dashboard__card-title">Open Leads By Stages</h3>
					</div>
					<div class="crm-dashboard__card-list">
						@forelse ($this->stageBreakdown as $stage)
							<div class="crm-dashboard__list-item">
								<div class="crm-dashboard__item-main">{{ $stage['label'] }}</div>
								<div class="crm-dashboard__item-value">{{ $stage['count'] }}</div>
							</div>
						@empty
							<div class="crm-dashboard__empty">No open leads are active in this range.</div>
						@endforelse
					</div>
				</article>

				<article class="crm-dashboard__panel crm-dashboard__card">
					<div class="crm-dashboard__card-header">
						<h3 class="crm-dashboard__card-title">Revenue By Sources</h3>
					</div>
					<div class="crm-dashboard__meter">
						@forelse ($this->sourceRevenue as $source)
							<div class="crm-dashboard__meter-row">
								<div class="crm-dashboard__meter-meta">
									<span>{{ $source['label'] }}</span>
									<span>{{ $this->formatMoney($source['value']) }}</span>
								</div>
								<div class="crm-dashboard__meter-track">
									<div class="crm-dashboard__meter-fill crm-dashboard__meter-fill--amber" style="width: {{ $source['width'] }}%"></div>
								</div>
							</div>
						@empty
							<div class="crm-dashboard__empty">No source revenue is available for this interval.</div>
						@endforelse
					</div>
				</article>

				<article class="crm-dashboard__panel crm-dashboard__card">
					<div class="crm-dashboard__card-header">
						<h3 class="crm-dashboard__card-title">Revenue By Stages</h3>
					</div>
					<div class="crm-dashboard__meter">
						@forelse ($this->statusRevenue as $status)
							<div class="crm-dashboard__meter-row">
								<div class="crm-dashboard__meter-meta">
									<span>{{ $status['label'] }}</span>
									<span>{{ $this->formatMoney($status['value']) }}</span>
								</div>
								<div class="crm-dashboard__meter-track">
									<div class="crm-dashboard__meter-fill crm-dashboard__meter-fill--sky" style="width: {{ $status['width'] }}%"></div>
								</div>
							</div>
						@empty
							<div class="crm-dashboard__empty">No stage revenue is available for this interval.</div>
						@endforelse
					</div>
				</article>
			</div>
		</section>
	</div>
</x-filament-panels::page>
